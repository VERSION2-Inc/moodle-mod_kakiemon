<?php
namespace ver2\kakiemon;
?>
<?php include 'header.php'; ?>
<form action="videoupload.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="key" value="<?php echo $key->keystring; ?>">
    <input type="file" name="video" accept="video/*">
    <input type="submit" value="<?php echo ke::str('upload'); ?>">
</form>
<?php include 'footer.php'; ?>
