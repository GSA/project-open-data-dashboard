<?php include 'header_meta_inc_view.php';?>

<?php include 'header_inc_view.php';?>

<?php include_once 'office_table_inc_view.php';?>


    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div>
          <h2>Digital Strategy</h2>

			<?php
			if(!empty($digitalstrategy)):

			    $sections = array("1.2.4", "1.2.5", "1.2.6", "1.2.7");


                foreach ($digitalstrategy as $office) {

        			if(!empty($office->digitalstrategy_status)) {

        				if ($office->digitalstrategy_status = json_decode($office->digitalstrategy_status)) {

        				    if($office->digitalstrategy_status->http_code == 200 && $digital_strategy = curl_from_json($office->digitalstrategy_status->url, false, true)) {

				                $office_strategy[$office->name] = $digital_strategy;

        				    }

        				}

        			}

                }



                foreach ($sections as $section) {

                    echo "<h1>$section</h1>";

                    foreach ($office_strategy as $agency => $strategy) {

                        foreach ($strategy->items as $item) {
                            if (!empty($item->id) && $item->id == $section) {
                                if($item->multiple === false) {
                                    echo "<h4>{$agency} - {$item->fields[0]->label}</h4>";
                                    echo '<br>';
                                    echo $item->fields[0]->value;
                                    echo '<hr>';
                                } else {

                                    $columns = count($item->fields);
                                    $rows   = count($item->fields[0]->value);

                                    echo '<h3 style="margin-top : 5em; background-color : #E8E8E8">' . "$agency</h3>";

                                    for ($row=0; $row < $rows; $row++) {

                                        echo '<table class="table table-striped table-hover" style="padding : 1em; margin-bottom : 4em; border-bottom : 3px solid #ccc">';

                                        for ($column=0; $column< $columns; $column++) {
                                            echo '<tr>';
                                            echo '<th class="col-sm-2 col-md-2 col-lg-2">' . "{$item->fields[$column]->label}</th>";

                                            echo '<td class="col-sm-10 col-md-10 col-lg-10">';
                                            if(!empty($item->fields[$column]->value[$row])) {
                                                echo $item->fields[$column]->value[$row];
                                            }
                                            echo "</td>";

                                            echo "</tr>";
                                        }

                                        echo '</table>';

                                    }

                                    echo '<hr>';
                                }


                            }
                        }

                    }

                    reset($office_strategy);

                }


                ?>



	        <?php
            endif;
			?>

        </div>
      </div>

      <hr>

<?php include 'footer.php'; ?>
