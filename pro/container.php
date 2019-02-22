<?php 

require_once( '../app/Mage.php' );
Mage::app();

$storeId = 3;

$path = 'bazaarvoice/born_container/container_source'; 
$containerSource = Mage::getStoreConfig($path ,$storeId);

$currentDomain = Mage::helper('core/url')->getCurrentUrl();
$currentPath = parse_url($currentDomain, PHP_URL_PATH);
$currentScheme = parse_url($currentDomain, PHP_URL_SCHEME);
$currentDomain = parse_url($currentDomain, PHP_URL_HOST);

$containerUrl = $currentScheme ? $currentScheme . '://' : '';
$containerUrl .= $currentDomain ? $currentDomain: '';
$containerUrl .= $currentPath ? $currentPath : '';

if(!$containerSource)
{
	$msg = 'BV Container source not found at ' . $path . 'for store id: ' . $storeId; 
	//Mage::log($msg);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="<?php echo $containerUrl ?>"/>
</head>
<body>
<script type="text/javascript" src="<?php echo $containerSource ?>">
</script>
<script>
    $BV.container('global', {});
</script>
</body>
</html>