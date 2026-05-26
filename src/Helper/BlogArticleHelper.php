<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;

class BlogArticleHelper
{
    protected ?bool $blogArticleTableExists = null;

    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function getAvailableTags(): array
    {
        if (!$this->blogArticleTableExists()) {
            return [];
        }

        $rows = $this->em->getConnection()->createQueryBuilder()
            ->select('tags')
            ->from('cms_content_blog_article')
            ->where('tags IS NOT NULL')
            ->andWhere("tags <> ''")
            ->executeQuery()
            ->fetchFirstColumn();

        $tags = [];
        foreach ($rows as $rowTags) {
            $tags = [...$tags, ...$this->normalizeTags($rowTags)];
        }

        natcasesort($tags);

        return array_values(array_unique($tags));
    }

    /**
     * @return ContentInterface[]
     */
    public function getRelatedArticles(string $tag, ?ContentInterface $currentContent = null, int $limit = 3, string $order = 'published_at_desc'): array
    {
        $tag = trim($tag);
        $order = $this->normalizeOrder($order);

        if ('' === $tag || $limit < 1 || !$this->blogArticleTableExists()) {
            return [];
        }

        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->select('DISTINCT a.id', 'a.tags', 'a.published_at')
            ->from('cms_content_blog_article', 'a')
            ->innerJoin('a', 'cms_content', 'c', 'c.id = a.id')
            ->andWhere('c.published_version_id IS NOT NULL')
            ->andWhere('a.tags IS NOT NULL')
            ->andWhere("a.tags <> ''")
            ->andWhere("LOWER(a.tags) LIKE :tag")
            ->setParameter("tag", "%".$this->escapeLikeParameter(mb_strtolower($tag))."%")
            ->setMaxResults(max($limit * 10, 25));

        match ($order) {
            'published_at_asc' => $qb->orderBy('a.published_at', 'ASC')->addOrderBy('a.id', 'ASC'),
            'name_asc' => $qb->orderBy('c.name', 'ASC')->addOrderBy('a.id', 'ASC'),
            'name_desc' => $qb->orderBy('c.name', 'DESC')->addOrderBy('a.id', 'DESC'),
            default => $qb->orderBy('a.published_at', 'DESC')->addOrderBy('a.id', 'DESC'),
        };

        if ($currentContent?->getId()) {
            $qb->andWhere('a.id <> :currentContentId')
                ->setParameter('currentContentId', $currentContent->getId());
        }

        $candidateRows = $qb->executeQuery()->fetchAllAssociative();
        $articleIds = [];

        foreach ($candidateRows as $candidateRow) {
            if (!$this->hasTag($candidateRow['tags'] ?? null, $tag)) {
                continue;
            }

            $articleIds[] = $candidateRow['id'];

            if (count($articleIds) >= $limit) {
                break;
            }
        }

        if ([] === $articleIds) {
            return [];
        }

        $articles = $this->em->getRepository(ContentInterface::class)->findBy(['id' => $articleIds]);
        $articlesById = [];

        foreach ($articles as $article) {
            if ($article instanceof ContentInterface && $article->getId()) {
                $articlesById[$article->getId()] = $article;
            }
        }

        $sortedArticles = [];
        foreach ($articleIds as $articleId) {
            if (isset($articlesById[$articleId])) {
                $sortedArticles[] = $articlesById[$articleId];
            }
        }

        return $sortedArticles;
    }

    public function normalizeTags(mixed $tags): array
    {
        if (is_array($tags)) {
            $normalizedTags = [];

            foreach ($tags as $tag) {
                $normalizedTags = [...$normalizedTags, ...$this->normalizeTags($tag)];
            }

            return array_values(array_unique($normalizedTags));
        }

        if (!is_scalar($tags) || '' === trim((string) $tags)) {
            return [];
        }

        $tags = trim((string) $tags);

        if (str_starts_with($tags, '[') || str_starts_with($tags, '{')) {
            $decodedTags = json_decode($tags, true);

            if (JSON_ERROR_NONE === json_last_error() && is_array($decodedTags)) {
                return $this->normalizeTags($decodedTags);
            }
        }

        $normalizedTags = preg_split('/\s*[,\n\r;]+\s*/', $tags) ?: [];
        $normalizedTags = array_map('trim', $normalizedTags);
        $normalizedTags = array_filter($normalizedTags, static fn (string $tag): bool => '' !== $tag);

        return array_values(array_unique($normalizedTags));
    }

    public function hasTag(mixed $tags, string $tag): bool
    {
        $normalizedTag = mb_strtolower(trim($tag));

        foreach ($this->normalizeTags($tags) as $availableTag) {
            if (mb_strtolower($availableTag) === $normalizedTag) {
                return true;
            }
        }

        return false;
    }

    public function normalizeOrder(string $order): string
    {
        return in_array($order, ['published_at_desc', 'published_at_asc', 'name_asc', 'name_desc'], true) ? $order : 'published_at_desc';
    }

    protected function escapeLikeParameter(string $value): string
    {
        return str_replace(["\\", "%", "_"], ["\\\\", "\\%", "\\_"], $value);
    }

    protected function blogArticleTableExists(): bool
    {
        if (null !== $this->blogArticleTableExists) {
            return $this->blogArticleTableExists;
        }

        return $this->blogArticleTableExists = $this->em->getConnection()->createSchemaManager()->tablesExist([
            'cms_content_blog_article',
        ]);
    }
}
