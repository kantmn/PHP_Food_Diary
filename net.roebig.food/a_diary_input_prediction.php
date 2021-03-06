<?
include_once('../func/php_header.php');
include_once('../func/php.php');

if( $user_cnt == 1 ){

	// receive string with words from ajax, split into words
	$pieces = explode(' ', trim( secure_sql($_POST['foods']) ));

	// Holt sich das letzte Teil/Wort aus der Eingabe
	$search_predicition = strtolower( array_pop($pieces) );

	if( isset( $pieces ) && strlen( $_POST['foods'] ) > 1 && !ctype_digit( $search_predicition ) ){
		//$q = 'SELECT name FROM `food_ingredients` WHERE CONCAT(" ", name, " ") like "%'.$search_predicition.'% " ';
		$q = 'SELECT food_ingredients.name
		FROM `food_ingredients`
		WHERE LENGTH(food_ingredients.name) > 1';
		$arr = sql($q);
		$ingredients = '';

		// looped through sql results and put all ingredients in string as lowercase
		while ( $row = $arr->fetch_assoc() ) {
			if( strlen( $row['name'] ) > 2 ){
				$ingredients .=strtolower($row['name'].' ');
			}
		}

		// split all ingredients to array, each ingredients per line
		$ingredients = preg_replace('/\s+/', ' ',$ingredients);
		$parts = array_unique( explode(' ', $ingredients) );

		if( count($parts) > 0 ){
			$predictions = array();
			$sortings = array();
			$lsv = array();
			
			// run through ingredients and output first 3
			foreach($parts as $key => $value) {
				$predictions[] = $value;
				$sort_val = 0;
				
				for( $i = 0; $i < strlen( $value ); $i++ ) {
					$current_char = substr($value, $i, 1);
					
					// switch to utf chars
					if( $current_char == 'ä' ){ $current_char = 'ae'; }
					if( $current_char == 'ü' ){ $current_char = 'ue'; }
					if( $current_char == 'ö' ){ $current_char = 'oe'; }
					if( $current_char == 'ß' ){ $current_char = 'ss'; }
					
					// bonus when same char at same position
					if( $current_char == substr($search_predicition, $i,1) ){
						$sort_val += 10 * $i;
					}
				}
				// check how many chars unique are identically
				$chars_sql = array_unique(str_split($value));
				$chars_sql_nr = count($chars_sql);
				$chars_input = array_unique(str_split($search_predicition));
				$chars_input_nr = count($chars_input);
				$differ_nr = count(array_diff($chars_input,$chars_sql));
				
				// check if used chars are not identically
				if( $chars_input_nr > $chars_sql_nr ){
					$sort_val -= 5 * $differ_nr;
				}
				// bonus when both strings have same length
				if( strlen($search_predicition) == strlen($value) ){
					$sort_val += 1 * strlen($value);
				}
				// big bonus when 100% match
				if( $value == $search_predicition){
					$sort_val += 10 * strlen($value);
				}
				// small bonus when string is inside search
				if( strpos($value, $search_predicition) !== false ){
					$sort_val += 5 * strlen($value);
				}
				
				// vergleiche unterschiede zwischen strings levenshtein(string1,string2,insert,replace,delete) 
				$sort_val -= levenshtein($value,$search_predicition,1,2,4);
				$lsv[] = levenshtein($value,$search_predicition,1,2,4);
				
				$sortings[] = $sort_val;
			}
			
			// sorts parts alphabetically
			array_multisort($sortings, SORT_DESC, $predictions);

			for( $i = 0; $i < sizeof($predictions); $i++){
				//outputs results with findings, and first letter is uppercase
				if( $sortings[$i] > 20 ){
					//echo '<div id="prediction_'.++$x.'">'.$sortings[$i].' - '.$lsv[$i].' '.ucfirst($predictions[$i]).' </div>';
					echo '<div id="prediction_'.++$x.'">'.ucfirst($predictions[$i]).' </div>';
				}
			}
		}
	}elseif( isset( $pieces ) && strlen( $_POST['foods'] ) > 1 && ctype_digit( $search_predicition ) ){
		$q = 'SELECT food_sets.name
		FROM food_sets
		WHERE barcode LIKE "%'.$search_predicition.'%" ORDER BY last_used DESC';
		$arr = sql($q);

		while ( $row = $arr->fetch_assoc() ) {
			echo '<div id="prediction_'.++$x.'">'.ucfirst($row['name']).' </div>';
		}
	}
}
?>