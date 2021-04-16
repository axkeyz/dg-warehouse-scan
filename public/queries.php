<?php

// Exit if directly accessed through /queries (may need to rewrite .htaccess)
if (!isset($_SERVER['HTTP_REFERER'])){ include('theme/header.html'); include('theme/404.php'); exit();}

// Include necessary classes
include_once('../classes/Query.php');
include_once('../config.php');

// Check if debugging is necessary
if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set out variables from AJAX request object (received from theme/functions/warehouse.php) + do some basic input cleaning
$location = strip_tags($_REQUEST['location']); 
$item = $_REQUEST['item'] ? array_unique(explode(",", strip_tags($_REQUEST['item']))) : null;
$save = isset($_REQUEST['save']) ? strip_tags($_REQUEST['save']) : null;
$get_errors = [];

// If there was a warehouse location sent through AJAX, then run this.
if(isset($location)){
    // Set out query from database. This gets the list of items from the database.
    $get_items = new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                            From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code
                            Where WarehouseLocation.Name = ? and (StockItem.Status = ? or StockItem.Status = ? or StockItem.Status = ?)", 
                            [$location, 'S', 'O', 'E']);
    
    $items = $get_items->get_results();
  
    if($items == null){
        // When no items are found, then check if the warehouse location actually exists or not. Throws an error of either a nonexistent location or an empty location.
        try {
            $location_code = (new Query("Select Code from WarehouseLocation where Name = ?", [$location]))->get_results();
            $location_error = 'This location was not found in the database. :(';
        }catch (Exception $e) {
            return false;
        }

        if(isset($location_code[0])){
            $location_code = $location_code[0]['Code'];
            $location_error = 'The database found an empty location! Is that right?';
        }
        
        // Add the location error to the list of errors
        $get_errors[] = $location_error;

    }else{
        // Since an item was found, get the location code.
        $location_code = (new Query("Select Code from WarehouseLocation where Name = ?", [$location]))->get_results()[0]['Code'];
        
        // Get column headers for making table headers later.
        $item_headers = $get_items->get_cols();
        
        // Gets all item codes of the items that were found in the database
        $bulk_transfer_itemcodes = implode('\n', array_column($items, 'Current Item Code'));
    }
}

// If there were item code(s) from AJAX, then run this.
if(isset($item)){
    $new_items_details = [];
    $new_items_details_checks = [];
    $saved = 'table-warning';
    
    // For every item
    foreach($item as $i){
        // Only checks items with a length greater than or equal to 7 characters
        if(strlen($i) >= 7){
            // Check for existing OriginalItemCode (the one that gets scanned)
            $details = (new Query("Select OriginalItemCode, ItemCode from StockItem where OriginalItemCode = ?", [$i]))->get_results();

            if(is_array($details)){ // If there is an item that has that has that original item code, then use the original item code to get the details of the new item
                $new_item_details = (new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                                From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where OriginalItemCode = ?", [$i]))->get_results()[0];
                if(! in_array($new_item_details, $new_items_details)){ // Only add if not a duplicated scan.
                    $new_items_details[] = $new_item_details;
                }
            }else{ // If no items were found with that original item code, then that item code has not been changed. Use the item code to get details of new item.
                $new_item_details = (new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where ItemCode = ?", [$i]))->get_results();
               
                if($new_item_details == null){ // If the item doesn't exist, add an error
                    $get_errors[] = "The Item Code ".$i." doesn't exist in the database. :(";
                }else{ // If the item does exist, then add to list of new item details.
                    $new_item_details = $new_item_details[0];
                    if(! in_array($new_item_details, $new_items_details)){ // Only add if not a duplicated scan
                        $new_items_details[] = $new_item_details;
                    }
                }
            }
            // add item to an array for checking later
            $new_items_details_check[$i] = $details;
        }
    }
}

// If AJAX requested to save items
if(isset($save) && $save != null){
    if(isset($new_items_details)){ // If there are new items to be saved
        foreach($new_items_details as $index => $new_item){
            // Get current item code in the loop
            $item_code = $new_item['Current Item Code'];
            // Update database depending whether referenced by original item code or by item code.
            if(isset($new_items_details_check[$item_code]) && is_array($new_items_details_check[$item_code])){
                new Query("Update StockItem Set Location = ? Where OriginalItemCode = ?", [$location_code, $item_code]);
            }else{
                new Query("Update StockItem Set Location = ? Where ItemCode = ?", [$location_code, $item_code]);
            }
            // Change location
            $new_items_details[$index]['Location'] = strtoupper($location);
            $saved = 'table-success';
        }
    }else{ // If no items were added, throws this error
        $get_errors[] = 'The item numbers field was super duper empty and thus nothing could be added to the database. :(';
    }
}
?>
<br />
<?php if(isset($get_errors) && $get_errors != null): ?>
    <!-- Show errors if there are any -->
    <?php foreach($get_errors as $error): ?>
        <div class="alert alert-warning" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php if(isset($saved) && $saved == 'table-success'): ?>
    <!-- Show success alert if items have been added -->
    <div class="alert alert-success" role="alert">
        Items have been saved to this location! :D
    </div>
<?php endif; ?>
<!-- Table of items in location: Lists # of items, # to be added and bulk transfer link -->
<div class="float-left">Number of Items: <?php echo $get_items->get_row_count() ?? '0'; ?></div><?php if(isset($new_items_details) && $new_items_details != null){ echo '<span class="text-danger font-weight-bold">&nbsp;+ '.count($new_items_details).' new items</span>';} ?>
<a href="#" class="float-right" onclick="document.getElementById('item_number').value += '\n<?php if(isset($bulk_transfer_itemcodes)): echo $bulk_transfer_itemcodes; endif;?>'">Select items for bulk transfer</a>
<?php if(isset($items) && is_array($items)): ?>
    <table class="table table-secondary">
        <thead class="thead-dark">
            <tr><!-- Set headers of table -->
                <?php foreach($item_headers as $header): ?>
                    <th scope="col"><?php echo $header['Name']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($new_items_details) && $new_items_details != null): ?>
                <!-- IItems to be added -->
                <?php foreach($new_items_details as $new_detail): ?>
                    <tr>
                        <?php foreach($new_detail as $new): ?>
                            <td class="<?php echo $saved; ?>"><?php echo $new; ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php foreach($items as $it): ?>
                <!-- Items in this location -->
                <tr>
                    <?php foreach($it as $i): ?>
                        <td><?php echo $i; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
