<?php
namespace ver2\kakiemon;
?>
<?php include 'header.php'; ?>
<p><?php echo $error; ?></p>
<?php
if ($CFG->debugdeveloper)
    echo '<div>'.$e->debuginfo.'</div>';
?>
<?php include 'footer.php'; ?>
