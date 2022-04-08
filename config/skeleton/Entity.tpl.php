<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Entity\Content;

<?php if (!$use_attributes || !$doctrine_use_attributes): ?>
/**
 * @ORM\Entity()
 * @ORM\Table(name="`<?= $table_name ?>`")
 */
<?php endif ?>
<?php if ($doctrine_use_attributes): ?>
    #[ORM\Entity(repositoryClass: <?= $repository_class_name ?>::class)]
    <?php if ($should_escape_table_name): ?>#[ORM\Table(name: '`<?= $table_name ?>`')]
    <?php endif ?>
<?php endif?>
class <?= $class_name ?> extends Content
{
    // map any custom field relative for <?= $class_name ."\n" ?>
}
