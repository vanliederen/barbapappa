<?php

use app\language\Language;
use app\language\LanguageManager;
use app\money\MoneyAmount;
use app\product\category\ProductCategory;
use app\product\category\ProductCategoryManager;
use app\product\Product;
use app\product\ProductManager;
use app\session\SessionManager;
use app\template\PageFooterBuilder;
use app\template\PageHeaderBuilder;
use carbon\core\util\StringUtils;

// Include the page top
require_once('top.php');

// Make sure the user is logged in
if(SessionManager::isLoggedIn())
    requireLogin();

// Get the action parameter if set
$a = null;
if(isset($_GET['a']))
    $a = trim($_GET['a']);

// TODO: Make sure the user has permission to do certain things!

if(StringUtils::equals($a, 'add', false)) {
    if(!isset($_POST['product-name'])) {
        ?>
        <div data-role="page" id="page-main">
            <?php PageHeaderBuilder::create(__('product', 'addProduct'))->setBackButton('productmanager.php')->build(); ?>

            <div data-role="main" class="ui-content">
                <p><?=__('product', 'fillInFieldsToAddProduct'); ?></p><br />

                <form method="POST" action="productmanager.php?a=add&step=2">
                    <label for="product-name"><?=__('product', 'productName'); ?>:</label>
                    <input name="product-name" id="product-name" value="" type="text">

                    <div data-role="collapsible">
                        <h4><?=__('product', 'nameTranslations'); ?></h4>
                        <?php
                        // Get all languages
                        $languages = LanguageManager::getLanguages();

                        // Create a field for all languages
                        foreach($languages as $language) {
                            // Validate the instance
                            if(!($language instanceof Language))
                                continue;

                            // Get the language tag
                            $tag = $language->getTag();

                            // Print the input field
                            echo '<label for="product-name-' . $tag . '">' . $language->get('language', 'thisLanguage') . ':</label>';
                            echo '<input name="product-name-' . $tag . '" id="product-name-' . $tag . '" value="" type="text" placeholder="' . __('general', 'optional') . '" data-clear-btn="true">';
                        }
                        ?>
                    </div>
                    <br />

                    <label for="product-category-id"><?=__('productCategory', 'category'); ?>:</label>
                    <select name="product-category-id" id="product-category-id">
                        <option value="" selected="selected"><?=__('productCategory', 'noCategory'); ?></option>
                        <?php
                        // Get the categories
                        $categories = ProductCategoryManager::getProductCategories();

                        // Print the items
                        foreach($categories as $category) {
                            // Make sure the instance is valid
                            if(!($category instanceof ProductCategory))
                                continue;

                            // Print the item
                            echo '<option value="' . $category->getId() . '">' . $category->getNameTranslated() . '</option>';
                        }
                        ?>
                    </select>
                    <br />

                    <label for="product-price"><?=__('product', 'price'); ?> &euro;:</label>
                    <input name="product-price" id="product-price" pattern="^\d+(\.|\,)\d{2}$" value="0.00" step="0.01" min="0" type="number">
                    <br />

                    <input type="submit" value="<?= __('product', 'addProduct'); ?>" class="ui-btn ui-icon-lock ui-btn-icon-right" />
                </form>
            </div>

            <?php PageFooterBuilder::create()->build(); ?>
        </div>
        <?php

    } else {
        // TODO: Get the translations

        // Get the values
        $productName = trim($_POST['product-name']);
        $productCategoryId = trim($_POST['product-category-id']);
        $productPrice = trim($_POST['product-price']);

        // Validate the product name
        if(strlen($productName) == 0)
            showErrorPage();

        // Get the product category instance
        $productCategory = null;

        // Validate the product ID
        if(strlen($productCategoryId) > 0) {
            // Make sure the product category ID exists
            if(!ProductCategoryManager::isProductCategoryWithId($productCategoryId))
                showErrorPage();

            // Get the product category instance
            $productCategory = new ProductCategory($productCategoryId);
        }

        // Remove all spaces from the string and replace comma's with full stops
        $productPrice = str_replace(' ', '', str_replace(',', '.', $productPrice));

        // Make sure the price is numeric
        if(!is_numeric($productPrice))
            showErrorPage();

        // Parse the price as float value
        $productPrice = floatval($productPrice) * 100;

        // Make sure the number is zero or positive
        if($productPrice < 0)
            showErrorPage();

        // Get the product price as money amount instance
        $productPrice = new MoneyAmount($productPrice);

        // Add the product
        ProductManager::createProduct($productCategory, $productName, $productPrice);

        ?>
        <div data-role="page" id="page-register" data-unload="false">
            <?php PageHeaderBuilder::create(__('product', 'addProduct'))->build(); ?>

            <div data-role="main" class="ui-content">
                <p>
                    <?= __('product', 'addedProductSuccessfully'); ?>
                </p><br />

                <fieldset data-role="controlgroup" data-type="vertical">
                    <a href="index.php" data-ajax="false" class="ui-btn ui-icon-home ui-btn-icon-left" data-direction="reverse"><?=__('navigation', 'goToFrontPage'); ?></a>
                </fieldset>
            </div>

            <?php PageFooterBuilder::create()->build(); ?>
        </div>
        <?php
    }

} else if(StringUtils::equals($a, 'change', false)) {
    // Make sure the product ID is set
    if(!isset($_GET['product_id']))
        showErrorPage();

    // Get the product ID and make sure it's valid
    $productId = $_GET['product_id'];
    if(!ProductManager::isProductWithId($productId))
        showErrorPage();

    // Get the product instance
    $product = new Product($productId);

    if(!isset($_POST['product-name'])) {
        ?>
        <div data-role="page" id="page-main">
            <?php PageHeaderBuilder::create(__('product', 'changeProduct'))->setBackButton('productmanager.php')->build(); ?>

            <div data-role="main" class="ui-content">
                <p><?=__('product', 'fillInFieldsToChangeProduct'); ?></p><br />

                <form method="POST" action="productmanager.php?a=change&product_id=<?=$productId; ?>&step=2">
                    <label for="product-name"><?=__('product', 'productName'); ?>:</label>
                    <input name="product-name" id="product-name" value="<?=$product->getName(); ?>" type="text">

                    <div data-role="collapsible">
                        <h4><?=__('product', 'nameTranslations'); ?></h4>
                        <?php
                        // Get all languages and translations
                        $languages = LanguageManager::getLanguages();
                        $translations = $product->getTranslations();

                        // Create a field for all languages
                        foreach($languages as $language) {
                            // Validate the instance
                            if(!($language instanceof Language))
                                continue;

                            // Get the language tag
                            $tag = $language->getTag();

                            // Determine the value
                            $value = '';
                            if($translations->hasTranslation($tag))
                                $value = $translations->getValue($tag);
                            // Print the input field
                            echo '<label for="product-name-' . $tag . '">' . $language->get('language', 'thisLanguage') . ':</label>';
                            echo '<input name="product-name-' . $tag . '" id="product-name-' . $tag . '" value="' . $value . '" type="text" placeholder="' . __('general', 'optional') . '" data-clear-btn="true">';
                        }
                        ?>
                    </div>
                    <br />

                    <label for="product-category-id"><?=__('productCategory', 'category'); ?>:</label>
                    <select name="product-category-id" id="product-category-id">
                        <option value=""><?=__('productCategory', 'noCategory'); ?></option>
                        <?php
                        // Get the categories
                        $categories = ProductCategoryManager::getProductCategories();
                        $currentCategory = $product->getProductCategory();

                        // Print the items
                        foreach($categories as $category) {
                            // Make sure the instance is valid
                            if(!($category instanceof ProductCategory))
                                continue;

                            // Determine whether to add a selected tag
                            $selected = '';
                            if($currentCategory !== null && $currentCategory->getId() == $category->getId())
                                $selected = 'selected="selected" ';

                            // Print the item
                            echo '<option value="' . $category->getId() . '" ' . $selected . '>' . $category->getNameTranslated() . '</option>';
                        }
                        ?>
                    </select>
                    <br />

                    <label for="product-price"><?=__('product', 'price'); ?> &euro;:</label>
                    <input name="product-price" id="product-price" pattern="^\d+(\.|\,)\d{2}$" value="<?=$product->getPrice()->getAmount() / 100; ?>" step="0.01" min="0" type="number">
                    <br />

                    <input type="submit" value="<?= __('product', 'changeProduct'); ?>" class="ui-btn ui-icon-lock ui-btn-icon-right" />
                </form>
            </div>

            <?php PageFooterBuilder::create()->build(); ?>
        </div>
        <?php

    } else {
        // TODO: Get the translations

        // Get the values
        $productName = trim($_POST['product-name']);
        $productCategoryId = trim($_POST['product-category-id']);
        $productPrice = trim($_POST['product-price']);

        // Validate the product name
        if(strlen($productName) == 0)
            showErrorPage();

        // Get the product category instance
        $productCategory = null;

        // Validate the product ID
        if(strlen($productCategoryId) > 0) {
            // Make sure the product category ID exists
            if(!ProductCategoryManager::isProductCategoryWithId($productCategoryId))
                showErrorPage();

            // Get the product category instance
            $productCategory = new ProductCategory($productCategoryId);
        }

        // Remove all spaces from the string and replace comma's with full stops
        $productPrice = str_replace(' ', '', str_replace(',', '.', $productPrice));

        // Make sure the price is numeric
        if(!is_numeric($productPrice))
            showErrorPage();

        // Parse the price as float value
        $productPrice = floatval($productPrice) * 100;

        // Make sure the number is zero or positive
        if($productPrice < 0)
            showErrorPage();

        // Get the product price as money amount instance
        $productPrice = new MoneyAmount($productPrice);

        // Set the name if it's different
        if(!StringUtils::equals($product->getName(), $productName, true))
            $product->setName($productName);

        // Set the product category if it's different
        if($product->getProductCategoryId() != $productCategoryId)
            $product->setProductCategory($productCategory);

        // Set the price if it's different
        if($product->getPrice()->getAmount() != $productPrice->getAmount())
            $product->setPrice($productPrice);

        ?>
        <div data-role="page" id="page-register" data-unload="false">
            <?php PageHeaderBuilder::create(__('product', 'changeProduct'))->build(); ?>

            <div data-role="main" class="ui-content">
                <p>
                    <?= __('product', 'changedProductSuccessfully'); ?>
                </p><br />

                <fieldset data-role="controlgroup" data-type="vertical">
                    <a href="index.php" data-ajax="false" class="ui-btn ui-icon-home ui-btn-icon-left" data-direction="reverse"><?=__('navigation', 'goToFrontPage'); ?></a>
                </fieldset>
            </div>

            <?php PageFooterBuilder::create()->build(); ?>
        </div>
        <?php
    }

} else if(isset($_GET['product_id'])) {
    // Get the product ID
    $productId = trim($_GET['product_id']);

    // Make sure the product ID
    if(!ProductManager::isProductWithId($productId))
        showErrorPage();

    // Get the product instance
    $product = new Product($productId);

    // Get the product category
    $category = $product->getProductCategory();

    ?>
    <div data-role="page" id="page-main">
        <?php PageHeaderBuilder::create(__('product', 'manageProduct'))->setBackButton('productmanager.php')->build(); ?>

        <div data-role="main" class="ui-content">
            <center>
                <table class="ui-responsive">
                    <tr>
                        <td><?=__('product', 'productName'); ?></td>
                        <td><?=$product->getName(); ?></td>
                    </tr>
                    <?php

                    // Get all product translations
                    $translations = $product->getTranslations();
                    $languages = $translations->getLanguages();

                    // Print a row for each language
                    foreach($languages as $language) {
                        // Validate the instance
                        if(!($language instanceof Language))
                            continue;

                        // Print the row
                        echo '<tr>';
                        echo '<td><i style="font-weight: normal; color: gray;">' . $language->get('language', 'thisLanguage') . '</i></td>';
                        echo '<td>' . $translations->getValue($language) . '</td>';
                        echo '</tr>';
                    }

                    ?>
                    <tr>
                        <td><?=__('productCategory', 'category'); ?></td>
                        <td>
                            <?php
                            // Print the category if there is any
                            if($category !== null)
                                echo $category->getNameTranslated();
                            else
                                echo '<i style="color: gray;">' . __('general', 'textNone') . '</i>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?=__('product', 'price'); ?></td>
                        <td><?=$product->getPrice()->getFormatted(); ?></td>
                    </tr>
                    <tr>
                        <td><?=__('product', 'creationDate'); ?></td>
                        <td><?=$product->getCreationDateTime()->toString(); ?></td>
                    </tr>
                    <?php
                    // Get the modification date time
                    $modificationDateTime = $product->getModifiedDateTime();

                    // Print the modification date time if set
                    if($modificationDateTime !== null) {
                        echo '<tr>';
                        echo '<td>' . __('product', 'modificationDate') . '</td>';
                        echo '<td>' . $modificationDateTime->toString() . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
            </center>
            <br />

            <p>
                <?=__('product', 'pressButtonToChangeOrDelete'); ?>
            </p><br />

            <?php if($category !== null): ?>
                <fieldset data-role="controlgroup" data-type="vertical">
                    <a href="productmanager.php?product_id=<?=$productId; ?>&a=change" class="ui-btn ui-icon-edit ui-btn-icon-left"><?=__('productCategory', 'viewProductCategory'); ?></a>
                </fieldset>
            <?php endif; ?>

            <fieldset data-role="controlgroup" data-type="vertical">
                <a href="productmanager.php?product_id=<?=$productId; ?>&a=change" class="ui-btn ui-icon-edit ui-btn-icon-left"><?=__('product', 'changeProduct'); ?></a>
                <a href="productmanager.php?v=<?=$productId; ?>&a=delete" class="ui-btn ui-icon-delete ui-btn-icon-left"><?=__('product', 'deleteProduct'); ?></a>
            </fieldset>
        </div>

        <?php PageFooterBuilder::create()->build(); ?>
    </div>
    <?php

} else {
    ?>
    <div data-role="page" id="page-main">
        <?php PageHeaderBuilder::create(__('product', 'manageProducts'))->setBackButton('index.php')->build(); ?>

        <div data-role="main" class="ui-content">
            <p>
                <?= __('product', 'clickOnProductToManageOrAdd'); ?>
            </p><br />

            <?php

            // Get all products
            $products = ProductManager::getProducts();

            // Print the list top
            echo '<ul class="ui-listview" data-role="listview" id="listview-stations-last-occupied" data-inset="true">';

            // Print the actual list of products
            if(sizeof($products) > 0):
                ?>
                <li data-role="list-divider"><?= __('product', 'products'); ?></li>
                <?php
                // Put all products in the list
                foreach($products as $product) {
                    // Validate the instance
                    if(!($product instanceof Product))
                        continue;

                    // Print the item
                    echo '<li><a class="ui-btn ui-btn-icon-right ui-icon-carat-r" href="productmanager.php?product_id=' .
                        $product->getId() . '">' . $product->getNameTranslated() . '</a></li>';
                }
            else:
                // There are no products yet, show a status message
                echo '<li><i>' . __('product', 'thereAreNoProducts') . '..</i></li>';
            endif;

            // Print the list bottom
            echo '</ul>';

            ?>

            <fieldset data-role="controlgroup" data-type="vertical">
                <a href="productmanager.php?a=add" class="ui-btn ui-icon-plus ui-btn-icon-left"><?= __('product',
                        'addProduct'); ?></a>
            </fieldset>
        </div>

        <?php PageFooterBuilder::create()->build(); ?>
    </div>
    <?php
}

// Include the page bottom
require_once('bottom.php');

