<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Entity\Content;

<?php if (!$use_attributes || !$doctrine_use_attributes) { ?>
/**
 * @ORM\Entity()
 * @ORM\Table(name="`<?php echo $table_name; ?>`")
 */
<?php } ?>
<?php if ($doctrine_use_attributes) { ?>
    #[ORM\Entity(repositoryClass: <?php echo $repository_class_name; ?>::class)]
    <?php if ($should_escape_table_name) { ?>#[ORM\Table(name: '`<?php echo $table_name; ?>`')]
    <?php } ?>
<?php }?>
class <?php echo $class_name; ?> extends Content
{
    // map any custom field relative for <?php echo $class_name."\n"; ?>
}
