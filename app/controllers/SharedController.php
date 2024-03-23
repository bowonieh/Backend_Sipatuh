<?php 

/**
 * SharedController Controller
 * @category  Controller / Model
 */
class SharedController extends BaseController{
	
	/**
     * guru_kelas_id_option_list Model Action
     * @return array
     */
	function guru_kelas_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT id AS value,nama AS label FROM kelas";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * kelas_tingkat_id_option_list Model Action
     * @return array
     */
	function kelas_tingkat_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT DISTINCT id AS value , id AS label FROM tingkat ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * kelas_jurusan_id_option_list Model Action
     * @return array
     */
	function kelas_jurusan_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT DISTINCT id AS value , id AS label FROM jurusan ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * pelanggaran_nis_option_list Model Action
     * @return array
     */
	function pelanggaran_nis_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT nis AS value,nis AS label FROM siswa";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * pelanggaran_jenis_id_option_list Model Action
     * @return array
     */
	function pelanggaran_jenis_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT id AS value,nama AS label FROM jenis_pelanggaran";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * siswa_kelas_id_option_list Model Action
     * @return array
     */
	function siswa_kelas_id_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT  DISTINCT id AS value,nama AS label FROM kelas";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * user_nama_value_exist Model Action
     * @return array
     */
	function user_nama_value_exist($val){
		$db = $this->GetModel();
		$db->where("nama", $val);
		$exist = $db->has("user");
		return $exist;
	}

	/**
     * user_email_value_exist Model Action
     * @return array
     */
	function user_email_value_exist($val){
		$db = $this->GetModel();
		$db->where("email", $val);
		$exist = $db->has("user");
		return $exist;
	}

	/**
     * wali_murid_siswa_nis_option_list Model Action
     * @return array
     */
	function wali_murid_siswa_nis_option_list(){
		$db = $this->GetModel();
		$sqltext = "SELECT DISTINCT nis AS value , nis AS label FROM siswa ORDER BY label ASC";
		$queryparams = null;
		$arr = $db->rawQuery($sqltext, $queryparams);
		return $arr;
	}

	/**
     * getcount_siswa Model Action
     * @return Value
     */
	function getcount_siswa(){
		$db = $this->GetModel();
		$sqltext = "SELECT COUNT(*) AS num FROM siswa";
		$queryparams = null;
		$val = $db->rawQueryValue($sqltext, $queryparams);
		
		if(is_array($val)){
			return $val[0];
		}
		return $val;
	}

	/**
     * getcount_pelanggaran Model Action
     * @return Value
     */
	function getcount_pelanggaran(){
		$db = $this->GetModel();
		$sqltext = "SELECT COUNT(*) AS num FROM pelanggaran";
		$queryparams = null;
		$val = $db->rawQueryValue($sqltext, $queryparams);
		
		if(is_array($val)){
			return $val[0];
		}
		return $val;
	}

	/**
     * getcount_hariini Model Action
     * @return Value
     */
	function getcount_hariini(){
		$db = $this->GetModel();
		$sqltext = "SELECT COUNT(*) AS num FROM pelanggaran WHERE tanggal >= CURRENT_DATE()";
		$queryparams = null;
		$val = $db->rawQueryValue($sqltext, $queryparams);
		
		if(is_array($val)){
			return $val[0];
		}
		return $val;
	}

	/**
	* doughnutchart_kategoripelanggaran Model Action
	* @return array
	*/
	function doughnutchart_kategoripelanggaran(){
		
		$db = $this->GetModel();
		$chart_data = array(
			"labels"=> array(),
			"datasets"=> array(),
		);
		
		//set query result for dataset 1
		$sqltext = "Select k.nama ,count(k.nama) as jumlah from jenis_pelanggaran k INNER JOIN pelanggaran p ON p.jenis_id = k.id GROUP BY k.nama limit 5";
		$queryparams = null;
		$dataset1 = $db->rawQuery($sqltext, $queryparams);
		$dataset_data =  array_column($dataset1, 'jumlah');
		$dataset_labels =  array_column($dataset1, 'nama');
		$chart_data["labels"] = array_unique(array_merge($chart_data["labels"], $dataset_labels));
		$chart_data["datasets"][] = $dataset_data;

		return $chart_data;
	}

	/**
	* piechart_pelanggaranhariini Model Action
	* @return array
	*/
	function piechart_pelanggaranhariini(){
		
		$db = $this->GetModel();
		$chart_data = array(
			"labels"=> array(),
			"datasets"=> array(),
		);
		
		//set query result for dataset 1
		$sqltext = "Select count(k.nama) as jumlah,k.nama from jenis_pelanggaran k INNER JOIN pelanggaran p ON p.jenis_id = k.id 
WHERE DATE_FORMAT(p.tanggal,'%Y-%m-%d') = CURRENT_DATE() GROUP BY k.nama";
		$queryparams = null;
		$dataset1 = $db->rawQuery($sqltext, $queryparams);
		$dataset_data =  array_column($dataset1, 'jumlah');
		$dataset_labels =  array_column($dataset1, 'nama');
		$chart_data["labels"] = array_unique(array_merge($chart_data["labels"], $dataset_labels));
		$chart_data["datasets"][] = $dataset_data;

		return $chart_data;
	}

	/**
	* barchart_rekapitulasipelanggaranperkelas Model Action
	* @return array
	*/
	function barchart_rekapitulasipelanggaranperkelas(){
		
		$db = $this->GetModel();
		$chart_data = array(
			"labels"=> array(),
			"datasets"=> array(),
		);
		
		//set query result for dataset 1
		$sqltext = "SELECT count(s.id) as jumlah, k.nama from pelanggaran p inner join siswa s on s.nis = p.nis INNER JOIN kelas k on k.id = s.kelas_id GROUP BY k.nama";
		$queryparams = null;
		$dataset1 = $db->rawQuery($sqltext, $queryparams);
		$dataset_data =  array_column($dataset1, 'jumlah');
		$dataset_labels =  array_column($dataset1, 'nama');
		$chart_data["labels"] = array_unique(array_merge($chart_data["labels"], $dataset_labels));
		$chart_data["datasets"][] = $dataset_data;

		return $chart_data;
	}

}
