<?
include_once('../func/php_header.php');
include_once('../func/php.php');
		
if( $user_cnt == 1 ){
	if( 
		isset($_POST['food_set_id']) AND 
		isset($_POST['food_set_firma']) AND 
		isset($_POST['food_set_name']) AND 
		isset($_POST['food_set_ingredients']) AND 
		isset($active_user) 
	){		
		$fsid = secure_sql($_POST['food_set_id']);
		$fsfirma = secure_sql($_POST['food_set_firma']);
		$fsingredients = secure_sql($_POST['food_set_ingredients']);
		$fsname = secure_sql($_POST['food_set_name']);
		$fsname = str_replace(" ","-",$fsname);
		
		$q = 'UPDATE food_sets SET 
		firma="'.$fsfirma.'", 
		name="'.$fsname.'", 
		ingredients = "'.$fsingredients.'"
		WHERE 
		id="'.$fsid.'"';
		$arr = sql($q);
		
		if( $arr ){
			echo '<div class="green_success">Food-Set ID '.$fsid.' has been updated successfully</div>';
		}else{
			echo '<div class="red_error">Error while updating food-set'.$fsid.' '.$fsfirma.'-'.$fsname.' </div>'.$arr;
		}
	}else{
		echo '<div class="red_error">Not all required fields received. Update aborted</div>';
	}
}
?>