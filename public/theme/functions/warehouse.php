<body>
    <script>
        // var item_number = document.getElementById("item_group");
        function fetch_location(save) {
            // Get location & item
            var location = document.getElementById('location').value;
            var item = document.getElementById('item_number').value.replace(/\r\n/g,'\n').split('\n');

            if (location.length < 4) { // remove item_group section when location length is too small
                document.getElementById("item_group").classList.add('d-none');
                return;
            } else {
                // Show item_group section when location length is long enough
                document.getElementById("item_group").classList.remove('d-none');
                // Get request url
                query = "/<?php echo APP_FOLDER; ?>/queries.php?";
                // Set up query components for AJAX call
                if(location){
                    query += 'location=' + location;
                }
                if(item){
                    document.getElementById("add_item").classList.remove('d-none');
                    query += '&item=' + item;
                }
                if(save){
                    query += '&save=' + save;
                }
                // Send ajax data to queries.php
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("results").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("GET", query, true);
                xmlhttp.send();
            }
        }
    </script>
    <a href="/<?php echo APP_FOLDER; ?>" class="text-center" style="color:#f88e1e"><h1>WH Transfer Items</h1></a><br />
    <div class="container">
    <div class="row">
        <div class="col-md"></div>
        <div class="col">
            <!-- Form for putting items in -->
            <form onsubmit="event.preventDefault();"><!-- Stop form from sending data when pressing send -->
                <div class="form-group"><!-- Warehouse location section -->
                    <label for="location">Select Location</label>
                    <input type="text" oninput="fetch_location()" id="location" class="form-control">
                </div>
                <div class="form-group d-none" id="item_group"><!-- Item group section -->
                    <label for="item_number">Item Number(s)</label>
                    <textarea type="text" oninput="fetch_location()" id="item_number" class="form-control" rows="5"></textarea>
                    <small class="form-text text-muted">Add 'em one per line.</small>
                </div>
                <div class="btn-group d-none" role="group" id="add_item" aria-label="Action" id="add_item"><!-- Button group to save/clear items -->
                    <button type="button" onclick="fetch_location('save')" class="btn btn-outline-primary">Transfer & save item(s)</button>
                    <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('item_number').value=''; fetch_location()">Clear item number(s)</button>
                </div>
            </form>
        </div>
        <div class="col-md"></div>
    </div>

    <!-- AJAX results placed here -->
    <div id="results"></div>
</body>
