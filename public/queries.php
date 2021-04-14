<?php

include_once('../classes/Query.php');
include_once('../config.php');

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$location = $_REQUEST['location'];
$item = $_REQUEST['item'] ?? '';
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
    if(strlen($item) >= 7){
        // Check for existing OriginalItemCode (the one that gets scanned)
        $details = (new Query("Select OriginalItemCode, ItemCode from StockItem where OriginalItemCode = ?", [$item]))->get_results();

        if(is_array($details)){
            $new_item_details = (new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                            From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where OriginalItemCode = ?", [$item]))->get_results()[0];
        }else{
            $new_item_details = (new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
            From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where ItemCode = ?", [$item]))->get_results();

            if($new_item_details == null){
                $get_errors[] = "Item Code";
            }else{
                $new_item_details = $new_item_details[0];
            }
        }       
        $saved = 'table-warning';
    }
}

if(isset($save) && $save != null){
    if(is_array($details)){
        new Query("Update StockItem Set Location = (?) Where OriginalItemCode = ?", [$location_code, $item]);
    }
    else{
        new Query("Update StockItem Set Location = (?) Where ItemCode = ?", [$location_code, $item]);
    }

    $new_item_details['Location'] = strtoupper($location);
    $saved = 'table-success';
}
?>

<br />
<?php if(isset($get_errors) && $get_errors != null): ?>
    <?php foreach($get_errors as $error): ?>
        <div class="alert alert-warning" role="alert">
            There may be an error in your input as <?php echo $error; ?> was not found in the database.
        </div>
    <?php endforeach; ?>
<?php endif; ?>
Number of Rows: <?php echo $get_items->get_row_count(); ?><br />
<?php if(isset($items) && is_array($items)): ?>
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <?php foreach($item_headers as $header): ?>
                    <th scope="col"><?php echo $header['Name']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($new_item_details) && $new_item_details != null): ?>
                <tr>
                    <?php foreach($new_item_details as $new_detail): ?>
                        <td class="<?php echo $saved; ?>"><?php echo $new_detail; ?></td>
                    <?php endforeach; ?>
                </tr>
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