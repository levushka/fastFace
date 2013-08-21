<?
namespace ff;

class tbl_fn {
	
	// RUN CLS PHP FUNCTIONS
	public static function run_cls_fn($fn_type, $php_fn_arr, $arg, $data, $row_id = 0, $affected_rows = 0) {
		if(empty($php_fn_arr) || !is_array($php_fn_arr) || empty($php_fn_arr[$fn_type]) || !is_array($php_fn_arr[$fn_type])) {
			return $data;
		}
		foreach ($php_fn_arr[$fn_type] as $key => $val) {
			$data = \ff\cls::run([
				$val[0],
				$val[1],
				[
					'fn_arg'=>$val[2],
					'fn_type'=>$fn_type,
					'arg'=>$arg,
					'data'=>$data,
					'row_id'=>$row_id,
					'affected_rows'=>$affected_rows,
					'out'=>'return'
				]
			]);
		}
		return $data;
	}
	
	
	// ORD COL
	public static function get_ord_sub_by_id($tbl_name, $pk, $row_id, $ord_arr) {
		if(!isset($ord_arr) || !is_array($ord_arr)) {
			throw new \Exception(sprintf('%s: ord_arr wrong. tbl[%s] pk[%s] id[%s]', __METHOD__, $tbl_name, $pk, $row_id));
		}
		
		$ord_sub = [];
		if(count($ord_arr) > 0) {
			$row = \ff\dbh::get_row('SELECT `'.implode('`, `', $ord_arr).'` FROM `'.$db_name.'`.`'.$tbl_name.'` WHERE '.$path.'.`'.$pk.'`='.(int)$row_id);
			foreach ($row as $key=>$val) {
				$ord_sub[] = '`'.$key.'`=\''.\ff\dbh::esc($val).'\'';
			}
			if(empty($ord_sub) || count($ord_arr) !== count($ord_sub)) {
				throw new \Exception(sprintf('%s: wrong ord_sub. tbl[%s] pk[%s] id[%s]', __METHOD__, $tbl_name, $pk, $row_id));
			}
		}
		return $ord_sub;
	}

	public static function get_ord_sub_by_data($cls, $data, $ord_arr) {
		if(!isset($ord_arr) || !is_array($ord_arr)) {
			throw new \Exception(sprintf('%s: ord_arr wrong. [%s]', __METHOD__, $cls));
		}
		
		$ord_sub = [];
		if(count($ord_arr) > 0) {
			if(empty($data) || !is_array($data)) {
				throw new \Exception(sprintf('%s: data obj wrong. [%s]', __METHOD__, $cls));
			}
			
			foreach ($ord_arr as $key=>$val) {
				if(empty($data[$val])) {
					throw new \Exception(sprintf('%s: data col wrong. [%s] col[%s]', __METHOD__, $cls, $val));
				}
				$ord_sub[] = '`'.\ff\dbh::esc($val).'`=\''.\ff\dbh::esc($data[$val]).'\'';
			}
			
			if(empty($ord_sub) || count($ord_arr) !== count($ord_sub)) {
				throw new \Exception(sprintf('%s: wrong ord_sub. [%s] [%s] [%s]', __METHOD__, $cls, var_export($ord_sub, TRUE), var_export($ord_arr, TRUE)));
			}
		}
		return $ord_sub;
	}

	public static function pre_ord($tbl_name, $col, $val, $ord_sub) {
		$row = \ff\dbh::get_row('SELECT count(*) as count_numb FROM `'.$db_name.'`.`'.$tbl_name.'` WHERE '.(empty($ord_sub) ? '' : implode(' AND ', $ord_sub).' AND ').'`'.$col.'`=\''.\ff\dbh::esc($val).'\'');
		if($row['count_numb'] > 0) {
			\ff\dbh::upd('UPDATE `'.$db_name.'`.`'.$tbl_name.'` SET `'.$col.'`=`'.$col.'`+1 WHERE '.(empty($ord_sub) ? '' : implode(' AND ', $ord_sub).' AND ').'`'.$col.'`>=\''.\ff\dbh::esc($val).'\'');
		}
	}

	public static function post_ord($db_name, $tbl_name, $col, $ord_sub) {
		if(!empty($col) && is_string($col)) {
			\ff\dbh::upd('UPDATE `'.$db_name.'`.`'.$tbl_name.'` SET `'.$col.'` = @reord := IFNULL(@reord, 0) + 1 '.(empty($ord_sub) ? '' : ' WHERE '.implode(' AND ', $ord_sub)).' ORDER BY `'.$col.'`');
		}
	}
	
	public static function max_ord($tbl_name, $col, $ord_sub) {
		if(!empty($col) && is_string($col)) {
			$res = \ff\dbh::get_row('SELECT MAX(`'.$col.'`) as max_ord FROM `'.$db_name.'`.`'.$tbl_name.'` '.(empty($ord_sub) ? '' : ' WHERE '.implode(' AND ', $ord_sub)), MYSQLI_ASSOC);
			if(!empty($res) && !empty($res['max_ord'])) {
				return (int)($res['max_ord'])+1;
			}
		}
		return 1;
	}
	
	// RETURN RESULT
	public static function return_result(array $arg, array $data, $err = NULL) {
		$out = !empty($arg['out']) ? $arg['out'] : ( FF_IS_OUT_JS ? 'js' : ( FF_IS_OUT_JSON ? 'json' : FF_OUTPUT) );
		
		if($out === 'js') {
			
			echo
				(
					!empty($arg['callback']) ? $arg['callback'] : 'fastFace.pid.done(\''.( !empty($arg['pid']) ? $arg['pid'] : 0 ).'\', '
				).
				json_encode($data).
				');'.PHP_EOL;
			
		} else if($out === 'json') {
			
			echo json_encode($data);
			
		} else if($out === 'return') {
			
			return $data;
			
		} else if($out === 'skip') {
			
			return NULL;
			
		} else if($out === 'csv') {
			
			$fp = fopen('php://output', 'w');
			if(!empty($arg['sel'])) {
				fputcsv($fp, $arg['sel'], ',', '"');
			}
			foreach ($data as $line) {
				fputcsv($fp, $line, ',', '"');
			}
			fclose($fp);
			
		} else if($out === 'xls') {

			static::send_xlsx($arg['sel'], $data);
			
		}
	}
	
	
	public static function send_xlsx($cols, $data, $file_name = 'output.xlsx') {
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		require_once(FF_PHPEXCEL_DIR.'/PHPExcel.php');
		require_once(FF_PHPEXCEL_DIR.'/PHPExcel/Writer/Excel2007.php');
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator('PHPExcel');
		$objPHPExcel->getProperties()->setLastModifiedBy('PHPExcel');
		$objPHPExcel->getProperties()->setTitle('Office 2007 XLSX Result Document');
		$objPHPExcel->getProperties()->setSubject('Office 2007 XLSX Result Document');
		$objPHPExcel->getProperties()->setDescription('Result document for Office 2007 XLSX, generated using PHPExcel');

		$objPHPExcel->setActiveSheetIndex(0);
		$objActiveSheet = $objPHPExcel->getActiveSheet();
		
		if(!empty($cols)) {
			foreach ($cols as $col => $val) {
				$objActiveSheet->setCellValueByColumnAndRow($col, 1, $val);
			}
		}

		foreach ($data as $row => $obj) {
			$i = 0;
			foreach ($obj as $fld_name => $val) {
				$objActiveSheet->setCellValueByColumnAndRow($i++, $row+2, $val);
			}
		}

		
		$objActiveSheet->setTitle('Result');

		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save('php://output');
			
	}

	public static function send_xls($cols, $data, $file_name = 'output.xls') {
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		echo xlsBOF();
		echo xlsCodepage(65001);
		foreach ($ck as $col => $val) {
			echo xlsWriteLabel(0, $col, $val);
		}
		foreach ($res_arr as $row => $obj) {
			$i = 0;
			foreach ($obj as $fld_name => $val) {
				echo xlsWriteLabel($row+1, $i++, $val);
			}
		}
		echo xlsEOF();
	}
	
}

