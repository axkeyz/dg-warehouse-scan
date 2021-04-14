<body>
    <script>
        // var item_number = document.getElementById("item_group");
        function fetch_location(location, item) {
            if (location.length < 3) {
                document.getElementById("item_group").classList.add('d-none');
                return;
            } else {
                // document.getElementById("results").innerHTML = str;
                document.getElementById("item_group").classList.remove('d-none');
                query = "/<?php echo APP_FOLDER; ?>/queries.php?";
                if(location){
                    query += 'location=' + location;
                }
                if(item){
                    query += '&item=' + item;
                }

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
    <h1>DG Warehouse Scan</h1>
    <h4 class="text-muted"><?php echo (new Query())->check_connection();?></h4>
    <div class="container">
    <div class="row">
        <div class="col"></div>
        <div class="col">
            <form>
                <div class="form-group">
                    <label for="location">Select Location</label>
                    <input type="text" onchange="fetch_location(this.value, document.getElementById('item_number').value)" id="location" class="form-control">
                </div>
                <div class="form-group d-none" id="item_group">
                    <label for="item_number">Item Number</label>
                    <input type="text" onchange="fetch_location(document.getElementById('location').value, this.value)" id="item_number" class="form-control">
                </div>
            </form>
        </div>
        <div class="col"></div>
    </div>

    <div id="results"></div>
</body>