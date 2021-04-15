<?php

include_once('../classes/Query.php');
include_once('../config.php');

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$location = $_REQUEST['location'];
$item = $_REQUEST['item'] ? explode(",", $_REQUEST['item']) : null;
$save = $_REQUEST['save'] ?? null;
$get_errors = [];

if(isset($location)){
    $get_items = new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                            From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code
                            Where WarehouseLocation.Name = ? and (StockItem.Status = ? or StockItem.Status = ? or StockItem.Status = ?)", 
                            [$location, 'S', 'O', 'E']);
    
    $items = $get_items->get_results();
    
    // $get_items = (new Query())->check_connection();
    if($get_items->results == null){
        $get_errors[] = "Warehouse Location";
    }else{
        $location_code = (new Query("Select Code from WarehouseLocation where Name = ?", [$location]))->get_results()[0]['Code'];

        $item_headers = $get_items->get_cols();
    }
}

if(isset($item)){
    $new_items_details = [];
    $new_items_details_checks = [];
    $saved = 'table-warning';
    foreach($item as $i){
        if(strlen($i) >= 7){
            // Check for existing OriginalItemCode (the one that gets scanned)
            $details = (new Query("Select OriginalItemCode, ItemCode from StockItem where OriginalItemCode = ?", [$i]))->get_results();

            if(is_array($details)){
                $new_item_details = (new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                                From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where OriginalItemCode = ?", [$i]))->get_results()[0];
                                $new_items_details[] = $new_item_details;
            }else{
                $new_item_details = (new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where ItemCode = ?", [$i]))->get_results();

                if($new_item_details == null){
                    $get_errors[] = "Item Code ".$i;
                }else{
                    $new_item_details = $new_item_details[0];
                    $new_items_details[] = $new_item_details;
                }
            }
            $new_items_details_check[$i] = $details;
        }
    }
}

if(isset($save) && $save != null){
    if(isset($new_items_details)){
        foreach($new_items_details as $index => $new_item){
            $item_code = $new_item['Current Item Code'];
            if(isset($new_items_details_check[$item_code]) && is_array($new_items_details_check[$item_code])){
                new Query("Update StockItem Set Location = ? Where OriginalItemCode = ?", [$location_code, $item_code]);
            }
            else{
                new Query("Update StockItem Set Location = ? Where ItemCode = ?", [$location_code, $item_code]);
            }

            $new_items_details[$index]['Location'] = strtoupper($location);
            $saved = 'table-success';
        }
    }else{
        $get_errors[] = 'the item numbers field was super duper empty and thus it';
    }
}
?>
<br />
<?php if(isset($get_errors) && $get_errors != null): ?>
    <?php foreach($get_errors as $error): ?>
        <div class="alert alert-warning" role="alert">
            There may be an error in your input as <?php echo $error; ?> was not found in the database. :(
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<?php if(isset($saved) && $saved == 'table-success'): ?>
    <div class="alert alert-success" role="alert">
        Items have been saved to this location! :D
    </div>
<?php endif; ?>
Number of Items: <?php echo $get_items->get_row_count(); ?><?php if(isset($new_items_details) && $new_items_details != null){ echo '<span class="text-danger font-weight-bold"> + '.count($new_items_details).' new items</span>';} ?>
<br/>
<?php if(isset($items) && is_array($items)): ?>
    <table class="table table-secondary">
        <thead class="thead-dark">
            <tr>
                <?php foreach($item_headers as $header): ?>
                    <th scope="col"><?php echo $header['Name']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($new_items_details) && $new_items_details != null): ?>
                    <?php foreach($new_items_details as $new_detail): ?>
                        <tr>
                            <?php foreach($new_detail as $new): ?>
                                <td class="<?php echo $saved; ?>"><?php echo $new; ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
            <?php endif; ?>
            <?php foreach($items as $it): ?>
                <tr>
                    <?php foreach($it as $i): ?>
                        <td><?php echo $i; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
