<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../app/bootstrap.php';
use Magento\Framework\App\Bootstrap;
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('adminhtml');
$importerModel = $obj->create('FireGento\FastSimpleImport\Model\Importer');
$productModel = $obj->create('\Magento\Catalog\Model\Product');
$categoryLinkManagement = $obj->create('\Magento\Catalog\Api\CategoryLinkManagementInterface');
$resourceConnection = $obj->get('\Magento\Framework\App\ResourceConnection')->getConnection();
$file1Path = '../var/import_csv/diagnosticswabs2.csv';
$file2Path = '../var/import_csv/medicalswabs2.csv';
$file3Path = '../var/import_csv/winzer_01_fluid_flow.csv';
echo '<pre>';

//import from first file
$f1Res = fopen($file1Path, 'r');
$inc = 0;
while (($data = fgetcsv($f1Res, 1000, ",")) !== FALSE) {
    break;
    $inc++;
    if($inc == 1){
        continue;
    }
    foreach($data as $field){
        $field = str_replace('¬Æ', '®', $field);

    }
    //var_dump($data); die;
    $productsArray = [
        [
            'sku' => $data[1],
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            'product_websites' => 'base',
            'name' => $data[8],
            'price' => $data[13],
            'qty' => $data[14],
            'model' => $data[0],
            'status' => 1,
            'c2c_upc' => $data[2],
            'upc' => $data[2],
            //'category_ids' => 759,
            'ean' => $data[3],
            'jan' => $data[4],
            'isbn' => $data[5],
            'mpn' => $data[6],
            'description' => $data[11],
            'meta_keyword' => $data[19],
            'weight' => $data[26],
            'c2c_width' => $data[26],
            'meta_title' => $data[44],
            'c2c_length' => $data[23]
            //'manufacturer' => 364,
        ],
    ];
    //var_dump($productsArray);
    try {
        $importerModel->processImport($productsArray);
    } catch (\Exception $e) {
        var_dump($e->getMessage()); die;
    }
    $productId = $productModel->getIdBySku($data[1]);
    $product = $productModel->load($productId);
    $product->setData('manufacturer', 364);
    $product->save();
    $categoriesTableName = $resourceConnection->getTableName('catalog_category_product');
    $query = "INSERT INTO ".$categoriesTableName." (category_id, product_id, position) VALUES (759, ".$product->getId().", 0) ON DUPLICATE KEY UPDATE category_id=759, product_id=".$product->getId().";";
    $resourceConnection->query($query);
    //$categoryLinkManagement->assignProductToCategories($data[1], array(759));
    //var_dump($product->getData());
    //die();
    //break;

}
fclose($f1Res);



//import from second file
$f2Res = fopen($file2Path, 'r');
$inc = 0;
while (($data = fgetcsv($f2Res, 1000, ",")) !== FALSE) {
    break;
    $inc++;
    if($inc == 1){
        continue;
    }
    foreach($data as $field){
        $field = str_replace('¬Æ', '®', $field);

    }
    //var_dump($data); die;
    $productsArray = [
        [
            'sku' => $data[1],
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            'product_websites' => 'base',
            'name' => $data[8],
            'price' => $data[13],
            'qty' => $data[14],
            'model' => $data[0],
            'status' => 1,
            'c2c_upc' => $data[2],
            'upc' => $data[2],
            //'category_ids' => 759,
            'ean' => $data[3],
            'jan' => $data[4],
            'isbn' => $data[5],
            'mpn' => $data[6],
            'description' => $data[11],
            'meta_keyword' => $data[19],
            'weight' => $data[26],
            'c2c_width' => $data[26],
            'meta_title' => $data[44],
            'c2c_length' => $data[23]
            //'manufacturer' => 364,
        ],
    ];
    //var_dump($productsArray);
    try {
        $importerModel->processImport($productsArray);
    } catch (\Exception $e) {
        var_dump($e->getMessage()); die;
    }
    $productId = $productModel->getIdBySku($data[1]);
    $product = $productModel->load($productId);
    $product->setData('manufacturer', 364);
    $product->save();
    $categoriesTableName = $resourceConnection->getTableName('catalog_category_product');
    $query = "INSERT INTO ".$categoriesTableName." (category_id, product_id, position) VALUES (759, ".$product->getId().", 0) ON DUPLICATE KEY UPDATE category_id=759, product_id=".$product->getId().";";
    $resourceConnection->query($query);
    //$categoryLinkManagement->assignProductToCategories($data[1], array(759));
    //var_dump($product->getData());
    //die();
    //break;
}
fclose($f2Res);

//import from third file
$f3Res = fopen($file3Path, 'r');
$inc = 0;
while (($data = fgetcsv($f3Res, 1000, ",")) !== FALSE) {
    break;
    $inc++;
    if($inc == 1){
        continue;
    }
    foreach($data as $field){
        $field = str_replace('¬Æ', '®', $field);

    }
    //var_dump($data); die;
    $productsArray = [
        [
            'sku' => $data[1],
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            'product_websites' => 'base',
            'name' => $data[8],
            'price' => $data[13],
            'qty' => $data[14],
            'model' => $data[0],
            'status' => 1,
            'c2c_upc' => $data[2],
            'upc' => $data[2],
            //'category_ids' => 759,
            'ean' => $data[3],
            'jan' => $data[4],
            'isbn' => $data[5],
            'mpn' => $data[6],
            'description' => $data[11],
            'meta_keyword' => $data[10],
            'weight' => $data[20],
            'c2c_width' => $data[18],
            'meta_title' => $data[27],
            'c2c_length' => $data[23]
            //'manufacturer' => 364,
        ],
    ];
    //var_dump($productsArray);
    try {
        $importerModel->processImport($productsArray);
    } catch (\Exception $e) {
        var_dump($e->getMessage()); die;
    }
    $productId = $productModel->getIdBySku($data[1]);
    $product = $productModel->load($productId);
    $product->setData('manufacturer', 2431);
    $product->save();
    $categoriesTableName = $resourceConnection->getTableName('catalog_category_product');
    $query = "INSERT INTO ".$categoriesTableName." (category_id, product_id, position) VALUES (728, ".$product->getId().", 0) ON DUPLICATE KEY UPDATE category_id=728, product_id=".$product->getId().";";
    $resourceConnection->query($query);
    //$categoryLinkManagement->assignProductToCategories($data[1], array(759));
    //var_dump($product->getData());
    //die();
    //break;

}
fclose($f3Res);

//import images
$f3Res = fopen($file3Path, 'r');
$inc = 0;
$directory = $obj->get('\Magento\Framework\Filesystem\DirectoryList');
$rootPath  =  $directory->getRoot();

$imagesPath = $rootPath.'/var/import_csv/images/Collective/';
while (($data = fgetcsv($f3Res, 1000, ",")) !== FALSE) {
    $inc++;
    if($inc == 1){
        continue;
    }
    foreach($data as $field){
        $field = str_replace('¬Æ', '®', $field);

    }
    $sku = $data[1];
    var_dump($sku);
    $productId = $productModel->getIdBySku($sku);
    $product = $productModel->load($productId);

    $productRepository = $obj->create('Magento\Catalog\Api\ProductRepositoryInterface');
    $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
    foreach ($existingMediaGalleryEntries as $key => $entry) {
        unset($existingMediaGalleryEntries[$key]);
    }
    $product->setMediaGalleryEntries($existingMediaGalleryEntries);
    $productRepository->save($product);
    /*Add Images To The Product*/
    $imagePath = $data[16]; // path of the image
    echo '<img src="'.$imagesPath.$imagePath.'">';

    /*var_dump($imagePath);
    try {
        $product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
        $product->save();
    } catch(Exception $e){

    }*/
    //die('ok');
    //$categoryLinkManagement->assignProductToCategories($data[1], array(759));
    //var_dump($product->getData());
    //die();
    //break;

}
fclose($f3Res);
