<?php 
/**
 * Main template file; generates the form
 */
 
//bootstrap generator
include( 'load.php' ); 

//if POSTing, load generator and exit
if ( !empty( $_POST ) )
	include DGS_BASE_DIR . '/includes/generate.php';

//output header
dgs_header();
?>

<h1>Slash Digital Strategy Generator</h1>

<div class="row">
	<div class="span3 offset4">
		<p id="import-prompt">Have an existing report? <a href="#" id="import-toggle">Import</a>.</p>
		<div class="well" id="import-upload">
			<p>Select a valid report JSON file to populate the below form.</p>
			<form id="upload-form" method="post" enctype="multipart/form-data">
				<input type="file" name="import" />
			</form>
		</div>
	</div>
</div>
<div class="row">
	<div class="span8 offset2">
<?php	

if (file_exists(DGS_REPORT_DIR . '/digitalstrategy.json')) {
  $_FILES['import']['tmp_name'] = DGS_REPORT_DIR . '/digitalstrategy.json';
  $_FILES['import']['autoimport'] = TRUE;
}

//init form
$form = Form::create()->setClass( 'form-horizontal' )->setMethod( 'post' );
$import = dgs_values();

//prepend agency dropdown
$agency = Form::select( 'agency' )->setLabel( 'Agency' );
$agency->add( '', '' ); //blank initial select

//loop through agencies and propegate
foreach ( $dgs_agencies as $option )
	$agency->add( $option->id, $option-> name );
	
$form->add( $agency );

//loop thorugh each action item
foreach ( $dgs_items as $item ) {

	//set up action-item class(es)
	$class = 'action-item';
	if ( $item->multiple )
		$class .= ' multiple';

	//manually add action-item div
	$form->add( '<div class="' . $class . '" id="' . $item->id . '">');
	
	//due date / permalink
	$form->add( Html::tag( 'div' )->setClass( 'due' )->setContent( Html::tag( 'a' )->setContent( $item->due )->setHref( '#' . $item->id ) ) );
	
	//action item header
	$tag = ( empty( $item->parent ) ) ? 'h3' : 'h4';
	$form->add( Html::tag( $tag )->setClass( 'heading' )->setContent( $item->id . ' ' . $item->text ) );
	
	//if there's no import, or the field's not in the import, just make one field
	//otherwise, make N field for each imported value
	for ( $i = 0, $max = dgs_max_values( $item, $import ); $i < $max; $i++ ) {
	
		//wrap fields in div so we can dupliate if a multiple response action item
		$class = ( $i == 0 ) ? 'fields' : 'fields border-top';
		$form->add( '<div class="' . $class . '">' );
	
		//loop through each action item's fields
		foreach ( $item->fields as $field ) {
			
			//must convert to string so we can call as a variable function
			$type = $field->type;
			
			//create the form field
			$name = ( $item->multiple ) ? $field->name . '[]' : $field->name;
			$form_field = Form::$type( $name )->setLabel( $field->label );
			    	
			//loop through options, if need be
			if ( !empty( $field->options ) ) {
			    
			    $form_field->add( '', '' ); //blank initial select
			
			    foreach ( $field->options as $option ) 
			    	$form_field->add( $option->value, $option->label );
			
			}
			
			//add the field to the form
			$form
			    ->add( $form_field );
				
			
		}
		
		//close fields wrapper div
		$form->add( '</div>' );
				
	}
	
	if ( $item->multiple ) 
		$form->add( Html::tag( 'a' )->setClass( 'add' )->setContent( 'Add Another' )->setHref( '#' ) );
	
	//close action-item wrapper div
	$form->add( '</div>' );
	
}

//importing
if ( !empty( $import ) )
	$form->populate( $import );

//Generate and Cancel buttons
$form->add( Html::tag('div')->setClass( 'form-actions' )->setContent( Html::tag('button')->setContent( 'Generate' )->setClass( 'btn btn-primary' ) . ' ' . Html::tag( 'button')->setClass( 'btn' )->setContent( 'Cancel' ) ) );

echo $form;	
?>
	</div>
</div>

<?php dgs_footer(); ?>
