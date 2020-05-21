<?php
global $wpdb;


// Table name
$tablename = $wpdb->prefix."shipping_kg_rate";

// Import CSV
if(isset($_POST['butimport'])){

  // File extension
  $extension = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);

  // If file extension is 'csv'
  if(!empty($_FILES['import_file']['name']) && ($extension == 'csv' || $extension == 'xls' || $extension == 'xlsx')){

    $totalInserted = 0;

    // Open file in read mode
    $csvFile = fopen($_FILES['import_file']['tmp_name'], 'r');

    fgetcsv($csvFile,0,";"); // Skipping header row

    $bol_truncated = false; 
    // Read file
    while(($csvData = fgetcsv($csvFile,0,";")) !== FALSE){
            if(!$bol_truncated){
                echo("<p>Reemplazando tarifas</p>");
                $wpdb->query("TRUNCATE TABLE $tablename"); 
                $bol_truncated = true;
            }

      //$csvData = array_map("utf8_encode", $csvData);
      //echo("<br>fetch row");


      // Row column length
      $dataLen = count($csvData);

      //echo("<br>dataLen: " . $dataLen);

      // Skip row if length != 4
      if( !($dataLen == 4) ) continue;
      // Assign value to variables
      $department = trim($csvData[0]);
      $citie = trim($csvData[1]);
      $weight = trim($csvData[2]);
      $kg_rate = trim($csvData[3]);
      // if(!isset($kg_rate) || !empty($kg_rate) || $kg_rate == 0 ){
      //   $kg_rate = 0;
      // }

      // Check record already exists or not
      //$cntSQL = "SELECT count(*) as count FROM {$tablename} where department='".$department."'";
      //$record = $wpdb->get_results($cntSQL, OBJECT);

     // if($record[0]->count==0){
        // Check if variable is empty or not
        if(!empty($department) && !empty($citie) && !empty($weight)) {
          // Insert Record
          $wpdb->insert($tablename, array(
            'department' =>$department,
            'citie' =>$citie,
            'weight' => $weight,
            'kg_rate' =>$kg_rate
          ));

          if($wpdb->insert_id > 0){
            $totalInserted++;
          }
        }
      //}
    }
    echo "<h3 style='color: green;'>Total registros importados : ".$totalInserted."</h3>";
  }else{
    echo "<h3 style='color: red;'>Extensión no valida.</h3>";
  }
}

?>
<h2>Todos los registros</h2>

<!-- Form -->
<form method='post' action='<?= $_SERVER['REQUEST_URI']; ?>' enctype='multipart/form-data'>
  <input type="file" name="import_file" >
  <input type="submit" name="butimport" value="Importar">
</form>

<!-- Record List -->
<table width='100%' border='1' style='border-collapse: collapse;'>
   <thead>
   <tr>
     <th>Id</th>
     <th>Cód. Departamento</th>
     <th>Municipio</th>
     <th>Peso</th>
     <th>Costo Envío</th>
   </tr>
   </thead>
   <tbody>
   <?php
   // Fetch records
   $entriesList = $wpdb->get_results("SELECT * FROM ".$tablename." order by id desc");
   if(count($entriesList) > 0){
     $count = 0;
     foreach($entriesList as $entry){
        $id = $entry->id;
        $department = $entry->department;
        $citie = $entry->citie;
        $weight = $entry->weight;
        $kg_rate = $entry->kg_rate;

        echo "<tr>
        <td>".++$count."</td>
        <td>".$department."</td>
        <td>".$citie."</td>
        <td>".$weight."</td>
        <td>".$kg_rate."</td>
        </tr>
        ";
     }
   }else{
     echo "<tr><td colspan='5'>No se encontraron tarifas</td></tr>";
  }
  ?>
  </tbody>
</table>