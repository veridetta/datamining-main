<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PDF;

use App\Models\M_Pengujian;
use App\Models\M_Penjualan;
use App\Models\M_Produk;
use App\Models\M_Support;
use App\Models\M_Nilai_Kombinasi;
use App\Models\Tbl_parameter;

class C_Apriori extends Controller
{
    public function setupPerhitunganApriori()
    {
        return view('main.apriori.setup');
    }

    public function tesRumus(){
        ini_set('memory_limit', '1024M');
        $jumlah_cluster=10;
        $maks_iterasi=100;
        $pembobot=2;
        $epsilon=0.0001;
        $iterasi_awal=1;
        $selisih_fungsi=11; //buat sample saja
        $selisih_fungsi_old=11; //buat sample saja
        $fungsi_objective_old='';
        $lt_old=array();
        $total_lt_old=array();
        $param=Tbl_parameter::get()->toArray();
        $fungsi_objective=1;
        //dd($param[0]);
        // Loop sebanyak $klaster untuk menambahkan elemen ke array
        $keanggotaan_cluster=array();
        for($i=0;$i<count($param);$i++){
            // Inisialisasi variabel untuk menampung elemen klaster
            $cluster = array();
        
            $sum = 0;

            for ($o = 0; $o < $jumlah_cluster-1; $o++) {
                $max = 1 - $sum;
                $random = mt_rand(0, 1000) / 1000 * $max;
                $cluster[] = $random;
                $sum += $random;
            }

            $cluster[] = 1 - $sum;
            //dd($cluster);
            $keanggotaan_cluster[]=$cluster;
        } 
        //mulai iterasi
        for($i=0;$i<=$maks_iterasi;$i++){
            if($selisih_fungsi>$epsilon){
                $miu_kuadrat='';
                $miu_kuadrat_x='';
                $total_miu_kuadrat='';
                $total_miu_kuadrat_x='';
                $pusat_cluster='';
                $xv='';
                $l='';
                $total_l='';
                $lt='';
                $total_lt='';
                $fungsi_objective='';
                //jika iterasi masih 1 tidak perlu generate anggota lagi
                if($i<1){
                    $tampung_param=$param;
                    //miu kuadrat ketemu
                    $miu_kuadrat=$keanggotaan_cluster;
                    foreach ($miu_kuadrat as &$row) {
                        foreach ($row as $key => $value) {
                            if (is_numeric($value)) {
                                $row[$key] *= $pembobot;
                            }
                        }
                    }
                    unset($value);
                    $miu_kuadrat_x=array();
                    $miu_kuadrat_bahan=array();
                    //miu kuadrat x KETEMU
                    $b=0;
                    foreach ($tampung_param as &$row) {
                        foreach ($row as $key => $value) {
                            $miu_kuadrat_x_awal=array();
                            for($k=0;$k<count($miu_kuadrat[$b]);$k++){
                                if (is_numeric($value)) {
                                    $miu_kuadrat_x_awal[]=$row[$key] * $miu_kuadrat[$b][$k];
                                }
                            }
                            //dd($miu_kuadrat_x_awal);
                            $row[$key] = $miu_kuadrat_x_awal;        
                                
                        }
                    }
                    unset($value);

                    $total_miu_kuadrat=array();
                    foreach ($miu_kuadrat as $arr) {
                        // Loop untuk mengakses setiap nilai dalam elemen array
                        foreach ($arr as $key => $val) {
                            // Jumlahkan nilai berdasarkan key yang sama
                            if (is_numeric($val)) {
                                if (isset($total_miu_kuadrat[$key])) {
                                    $total_miu_kuadrat[$key] += $val;
                                } else {
                                    $total_miu_kuadrat[$key] = $val;
                                }
                            }
                        }
                    }
                    unset($val);
                    //total miu kuadrat x ketemu
                    $total_miu_kuadrat_x=array();
                    foreach ($tampung_param as $arr) {
                        // Loop untuk mengakses setiap nilai dalam elemen array
                        foreach ($arr as $x => $val) {
                            //dd($arr["type_mobil"][0]);
                            for($j=0;$j<count($val);$j++){
                                if (is_numeric($arr[$x][$j])) {
                                    if (isset($total_miu_kuadrat_x[$x][$j])) {
                                        $total_miu_kuadrat_x[$x][$j] += $arr[$x][$j];
                                    } else {
                                        $total_miu_kuadrat_x[$x][$j] = $arr[$x][$j];
                                    }
                                }
                            }
                        }
                    }
                    unset($val);
                    //pusat Cluster ketemu
                    $pusat_cluster=array();
                    foreach($total_miu_kuadrat as $key => $val){
                        foreach($total_miu_kuadrat_x as $x => $k){
                            for($j=0;$j<count($k);$j++){
                                if (is_numeric($k[$j])) {
                                    if (isset($pusat_cluster[$x][$j])) {
                                        
                                    }else{
                                        $hasil = $k[$j]/$val;
                                        $pusat_cluster[$x][$j] = $hasil;
                                        //echo "Pusat Cluster {$x} dan {$j} Miu Kuadrat X ".$k[$j]." / Miu Kuadrat ". $val." =". $hasil .'<br>';
                                    }
                                }
                            }
                        }
                    }
                    unset($k);
                    $xv=array();
                    $n=0;
                    foreach($param as $arr ){
                        foreach($arr as $k => $v){
                            if(is_numeric($v)){
                                for($j=0;$j<count($pusat_cluster[$k]);$j++){
                                    $kiri=($v-$pusat_cluster[$k][$j])*($v-$pusat_cluster[$k][$j]);
                                    //echo "Mobil {$k} dengan value {$v} dengan pusat cluster {$j} dengan value {$pusat_cluster[$k][$j]} hasilnya {$kiri}<br>";
                                    if (isset($xv[$n][$j])) {
                                        $xv[$n][$j] += $kiri;
                                    } else {
                                        $xv[$n][$j] = $kiri;
                                    }
                                }
                            }
                            
                        }
                        $n++;
                    }
                    unset($v);
                    $l=array();
                    for($j=0;$j<count($miu_kuadrat);$j++){
                        for($x=0;$x<count($miu_kuadrat[$j]);$x++){
                            $val = $miu_kuadrat[$j][$x]*$xv[$j][$x];
                            //echo "value {$miu_kuadrat[$j][$x]} dikali {$xv[$j][$x]} = {$val} <br>";
                            if (isset($l[$j][$x])) {
                                $l[$j][$x] += $kiri;
                            } else {
                                $l[$j][$x] = $kiri;
                            } 
                        }
                    }
                    $total_l=array();
                    $k=0;
                    foreach ($l as $arr => $val) {
                        foreach($val as $v){
                            if (is_numeric($v)) {
                                if (isset($total_l[$k])) {
                                    $total_l[$k] += $v;
                                } else {
                                    $total_l[$k] = $v;
                                }
                            }
                        }
                        $k++;
                    }
                    unset($val);
                    unset($v);
                    $lt = array();
                    $total_lt=array();
                    for($k=0;$k<count($xv);$k++){
                        $vv=0;
                        for($n=0;$n<count($xv[$k]);$n++){
                            $xxx = pow($xv[$k][$n],(-1/($pembobot-1)));
                            if(isset($lt[$k][$n])){
                                echo "NOSET {$k} dan {$n}";
                            }else{
                                $lt[$k][$n] = $xxx;
                            }
                            $vv += $xxx;
                        }
                        $total_lt[$k]=$vv;
                    }
                    $fungsi_objective=0;
                    foreach($total_l as $arr => $val){
                        $fungsi_objective += $val;
                    }
                    $fungsi_objective_old=$fungsi_objective;
                    $lt_old=$lt;
                    $total_lt_old=$total_lt;
                    unset($val);
                    /*
                    dump('Data');
                    dump($param);
                    dump('Keanggotaan Cluster');
                    dump($keanggotaan_cluster);
                    dump('Miu Kuadrat');
                    dump($miu_kuadrat);
                    dump('Miu Kuadrat X');
                    dump($tampung_param);
                    dump('Total Miu Kuadrat');
                    dump($total_miu_kuadrat);
                    dump('Total Miu Kuadrat X');
                    dump($total_miu_kuadrat_x);
                    dump('Pusat Cluster');
                    dump($pusat_cluster);
                    dump('X_V');
                    dump($xv);
                    dump('L');
                    dump($xv);
                    dump('Total L');
                    dump($total_l);
                    dump('LT');
                    dump($lt);
                    dump('Total LT');
                    dump($total_lt);
                    dump('Fungsi Objective');
                    dump($fungsi_objective);
                    */
                    $selisih_fungsi=$fungsi_objective-0;
                    //$selisih_fungsi_old=$selisih_fungsi;
                    $maks = $i+1;
                    if($selisih_fungsi<$epsilon){
                        for($b=0;$b<count($l);$b++){
                            foreach($param[$b] as $arr => $val){
                                if(is_numeric($val)){

                                }else{
                                    $max_value = max($l[$b]);
                                    $max_key = array_search($max_value, $l[$b]);
                                    $final[$b][$val]=$max_key;
                                }
                            }
                        }
                         // mengambil nilai dari indeks 1 hingga 9
                        dump ("Ditemukan pada iterasi ke {$maks}. Dengan Selisih Fungsi Objektif");
                        dump($selisih_fungsi);
                        dump('Data Clustering');
                        dump($final);
                        break;   
                    }else{                        
                        dump ("Belum ditemukan pada iterasi ke {$maks}. Dengan Selisih Fungsi Objektif");
                        dump($selisih_fungsi);   
                    }
                }else{
                    $tampung_param=$param;
                    $keanggotaan_cluster=array();
                    for($p=0;$p<count($lt_old);$p++){
                        for($q=0;$q<count($lt_old[$p]);$q++){
                            $vvv=$lt_old[$p][$q]/$total_lt_old[$p];
                            $keanggotaan_cluster[$p][$q]=$vvv;
                        }
                    }
                   
                    //miu kuadrat ketemu
                    $miu_kuadrat=$keanggotaan_cluster;
                    foreach ($miu_kuadrat as &$row) {
                        foreach ($row as $key => $value) {
                            if (is_numeric($value)) {
                                $row[$key] *= $pembobot;
                            }
                        }
                    }
                    unset($value);
                    $miu_kuadrat_x=array();
                    $miu_kuadrat_bahan=array();
                    //miu kuadrat x KETEMU
                    $b=0;
                    foreach ($tampung_param as &$row) {
                        foreach ($row as $key => $value) {
                            $miu_kuadrat_x_awal=array();
                            for($k=0;$k<count($miu_kuadrat[$b]);$k++){
                                if (is_numeric($value)) {
                                    $miu_kuadrat_x_awal[]=$row[$key] * $miu_kuadrat[$b][$k];
                                }
                            }
                            //dd($miu_kuadrat_x_awal);
                            $row[$key] = $miu_kuadrat_x_awal;        
                                
                        }
                    }
                    unset($value);

                    $total_miu_kuadrat=array();
                    foreach ($miu_kuadrat as $arr) {
                        // Loop untuk mengakses setiap nilai dalam elemen array
                        foreach ($arr as $key => $val) {
                            // Jumlahkan nilai berdasarkan key yang sama
                            if (is_numeric($val)) {
                                if (isset($total_miu_kuadrat[$key])) {
                                    $total_miu_kuadrat[$key] += $val;
                                } else {
                                    $total_miu_kuadrat[$key] = $val;
                                }
                            }
                        }
                    }
                    unset($val);
                    //total miu kuadrat x ketemu
                    $total_miu_kuadrat_x=array();
                    foreach ($tampung_param as $arr) {
                        // Loop untuk mengakses setiap nilai dalam elemen array
                        foreach ($arr as $x => $val) {
                            //dd($arr["type_mobil"][0]);
                            for($j=0;$j<count($val);$j++){
                                if (is_numeric($arr[$x][$j])) {
                                    if (isset($total_miu_kuadrat_x[$x][$j])) {
                                        $total_miu_kuadrat_x[$x][$j] += $arr[$x][$j];
                                    } else {
                                        $total_miu_kuadrat_x[$x][$j] = $arr[$x][$j];
                                    }
                                }
                            }
                        }
                    }
                    unset($val);
                    //pusat Cluster ketemu
                    $pusat_cluster=array();
                    foreach($total_miu_kuadrat_x as $key => $val){
                        for($x=0;$x<count($total_miu_kuadrat);$x++){
                            if(is_numeric($total_miu_kuadrat_x[$key][$x])){
                                $hasil=$total_miu_kuadrat_x[$key][$x]/$total_miu_kuadrat[$x];
                                $pusat_cluster[$key][$x]=$hasil;
                            }
                        }
                    }
                    
                    unset($k);
                    $xv=array();
                    $n=0;
                    foreach($param as $arr ){
                        foreach($arr as $k => $v){
                            if(is_numeric($v)){
                                for($j=0;$j<count($pusat_cluster[$k]);$j++){
                                    $kiri=($v-$pusat_cluster[$k][$j])*($v-$pusat_cluster[$k][$j]);
                                    //echo "Mobil {$k} dengan value {$v} dengan pusat cluster {$j} dengan value {$pusat_cluster[$k][$j]} hasilnya {$kiri}<br>";
                                    if (isset($xv[$n][$j])) {
                                        $xv[$n][$j] += $kiri;
                                    } else {
                                        $xv[$n][$j] = $kiri;
                                    }
                                }
                            }
                            
                        }
                        $n++;
                    }
                    unset($v);
                    $l=array();
                    for($j=0;$j<count($miu_kuadrat);$j++){
                        for($x=0;$x<count($miu_kuadrat[$j]);$x++){
                            $val = $miu_kuadrat[$j][$x]*$xv[$j][$x];
                            //echo "value {$miu_kuadrat[$j][$x]} dikali {$xv[$j][$x]} = {$val} <br>";
                            if (isset($l[$j][$x])) {
                                $l[$j][$x] += $val;
                            } else {
                                $l[$j][$x] = $val;
                            } 
                        }
                    }
                    $total_l=array();
                    $k=0;
                    foreach ($l as $arr => $val) {
                        foreach($val as $v){
                            if (is_numeric($v)) {
                                if (isset($total_l[$k])) {
                                    $total_l[$k] += $v;
                                } else {
                                    $total_l[$k] = $v;
                                }
                            }
                        }
                        $k++;
                    }
                    unset($val);
                    unset($v);
                    $lt = array();
                    $total_lt=array();
                    for($k=0;$k<count($xv);$k++){
                        $vv=0;
                        for($n=0;$n<count($xv[$k]);$n++){
                            $xxx = pow($xv[$k][$n],(-1/($pembobot-1)));
                            if(isset($lt[$k][$n])){
                                echo "NOSET {$k} dan {$n}";
                            }else{
                                $lt[$k][$n] = $xxx;
                            }
                            $vv += $xxx;
                        }
                        $total_lt[$k]=$vv;
                    }
                    $fungsi_objective=0;
                    for($b=0;$b<count($total_l);$b++){
                        $fungsi_objective += $total_l[$b];
                    }
                    $selisih_fungsi = $fungsi_objective_old-$fungsi_objective;
                    $fungsi_objective_old=$fungsi_objective;
                    $lt_old=$lt;
                    $total_lt_old=$total_lt;
                    /*
                    dump("ITerasi {$i}");
                    dump('Data');
                    dump($param);
                    dump('Keanggotaan Cluster');
                    dump($keanggotaan_cluster);
                    dump('Miu Kuadrat');
                    dump($miu_kuadrat);
                    dump('Miu Kuadrat X');
                    dump($tampung_param);
                    dump('Total Miu Kuadrat');
                    dump($total_miu_kuadrat);
                    dump('Total Miu Kuadrat X');
                    dump($total_miu_kuadrat_x);
                    dump('Pusat Cluster');
                    dump($pusat_cluster);
                    dump('X_V');
                    dump($xv);
                    dump('L');
                    dump($l);
                    dump('Total L');
                    dump($total_l);
                    dump('LT');
                    dump($lt);
                    dump('Total LT');
                    dump($total_lt);
                    dump('Fungsi Objective');
                    dump($fungsi_objective);
                    
                    */
                    $maks = $i+1;
                    $final = array();
                    if($selisih_fungsi<$epsilon){
                        for($b=0;$b<count($l);$b++){
                            foreach($param[$b] as $arr => $val){
                                if(is_numeric($val)){

                                }else{
                                    $max_value = max($l[$b]);
                                    $max_key = array_search($max_value, $l[$b]);
                                    $final[$b][$val]=$max_key;
                                }
                            }
                        }
                         // mengambil nilai dari indeks 1 hingga 9
                        dump ("Ditemukan pada iterasi ke {$maks}. Dengan Selisih Fungsi Objektif");
                        dump($selisih_fungsi);
                        dump('Data Clustering');
                        dump($final);
                        break;   
                    }else{                        
                        dump ("Belum ditemukan pada iterasi ke {$maks}. Dengan Selisih Fungsi Objektif");
                        dump($selisih_fungsi);   
                    }
                }
            }else{
                $maks = $i+1;
               dump ("Ditemukan pada iterasi ke {$maks}. Dengan Selisih Fungsi Objektif");
               dump($selisih_fungsi_old);
               break;
               
            }
            $penuh = $maks_iterasi-1;
            if($i == $penuh){
                echo "Tidak berhasil menemukan epsilon";
            }
        }
    }
    public function multiply_array_by_number($array, $number) {
        foreach ($array as &$sub_array) { // loop through each sub-array
            foreach ($sub_array as &$value) { // loop through each value in sub-array
                $value *= $number; // multiply the value with the given number
            }
        }
        return $array;
    }
    public function prosesAnalisaApriori(Request $request)
    {
        $minSupp = $request -> support;
        $minConfidence = $request -> confidence;
        // 'support': support,
        //     'confidence': confidence,
        //     'nama' : nama
        // $
        // insert data pengujian 
        $kdPengujian = Str::uuid();
        $pengujian = new M_Pengujian();
        $pengujian -> kd_pengujian = $kdPengujian;
        $pengujian -> nama_penguji = $request -> nama;
        $pengujian -> min_supp = $minSupp;
        $pengujian -> min_confidence = $minConfidence;
        $totalProduk = M_Produk::count();
        // cari nilai support 
        $dataProduk = M_Produk::all();
        foreach($dataProduk as $produk){
            $kdProduk = $produk -> kd_produk;
            $totalTransaksi = M_Penjualan::where('kd_barang', $kdProduk) -> count();
            $nSupport = ($totalTransaksi / $totalProduk) * 100;
            $supp = new M_Support();
            $supp -> kd_pengujian = $kdPengujian;
            $supp -> kd_produk = $kdProduk;
            $supp -> support = $nSupport;
            $supp -> save();
        }
        // kombinasi 2 item set 
        $qProdukA = M_Support::where('kd_pengujian', $kdPengujian) -> where('support', '>=', $minSupp) -> get();
        foreach($qProdukA as $qProdA){
            $kdProdukA = $qProdA -> kd_produk;
            $qProdukB = M_Support::where('kd_pengujian', $kdPengujian) -> where('support', '>=', $minSupp) -> get();
            foreach($qProdukB as $qProdB){
                $kdProdukB = $qProdB -> kd_produk;
                $jumB = M_Nilai_Kombinasi::where('kd_barang_a', $kdProdukB) -> count();
                if($jumB > 0){

                }else{
                    if($kdProdukA == $kdProdukB){

                    }else{
                        $kdKombinasi = Str::uuid();
                        $nk = new M_Nilai_Kombinasi();
                        $nk -> kd_pengujian = $kdPengujian;
                        $nk -> kd_kombinasi = $kdKombinasi;
                        $nk -> kd_barang_a = $kdProdukA;
                        $nk -> kd_barang_b = $kdProdukB;
                        $nk -> jumlah_transaksi = 0;
                        $nk -> support = 0;
                        $nk -> save();
                    }
                }
            }
        }

        // kombinasi 2 itemset phase 2
        $nilaiKombinasi = M_Nilai_Kombinasi::where('kd_pengujian', $kdPengujian) -> get();
        $no = 1;
        foreach($nilaiKombinasi as $nk){
            $kdKombinasi = $nk -> kd_kombinasi;
            $kdBarangA = $nk -> kd_barang_a;
            $kdBarangB = $nk -> kd_barang_b;

            // cari total transaksi 
            $dataFaktur = M_Penjualan::distinct() -> get(['no_faktur']);
            $fnTransaksi = 0;
            foreach($dataFaktur as $faktur){
                $noFaktur = $faktur -> no_faktur;
                $qBonTransaksiA = M_Penjualan::where('no_faktur', $noFaktur) -> where('kd_barang', $kdBarangA) -> count();
                $qBonTransaksiB = M_Penjualan::where('no_faktur', $noFaktur) -> where('kd_barang', $kdBarangB) -> count();
                if($qBonTransaksiA == 1 && $qBonTransaksiB == 1){
                    $fnTransaksi++;
                }
            }
            $support = ($fnTransaksi / $totalProduk) * 100;
            M_Nilai_Kombinasi::where('kd_pengujian', $kdPengujian) -> where('kd_kombinasi', $kdKombinasi) -> update([
                'jumlah_transaksi' => $fnTransaksi,
                'support' => $support
            ]);
            // for($x = 1; $x <= $totalFaktur; $x++){
            //     $bonTransaksi1 = M_Penjualan::where('no')
            // }

        }

        $pengujian -> save();
        $dr = ['status' => 'sukses', 'kdPengujian' => $kdPengujian];
        return \Response::json($dr);
    }

    public function hasilAnalisa(Request $request, $kdPengujian)
    {
        $dataPengujian = M_Pengujian::where('kd_pengujian', $kdPengujian) -> first();
        $dataSupportProduk = M_Support::where('kd_pengujian', $kdPengujian) -> get();
        $dataMinSupp = M_Support::where('kd_pengujian', $kdPengujian) -> where('support', '>=', $dataPengujian -> min_supp) -> get();
        $dataKombinasiItemset = M_Nilai_Kombinasi::where('kd_pengujian', $kdPengujian) -> get();
        $dataMinConfidence = M_Nilai_Kombinasi::where('kd_pengujian', $kdPengujian) -> where('support', '>=', $dataPengujian -> min_confidence) -> get();
        $totalProduk = M_Produk::count();
        // dd($dataSupportProduk);
        $dr = [
            'dataSupport' => $dataSupportProduk, 
            'totalProduk' => $totalProduk, 
            'dataPengujian' => $dataPengujian,
            'dataMinSupport' => $dataMinSupp,
            'dataKombinasiItemset' => $dataKombinasiItemset,
            'dataMinConfidence' => $dataMinConfidence,
            'kdPengujian' => $kdPengujian
        ];
        return view('main.apriori.hasilAnalisa', $dr);
    }

    public function cetakAnalisa(Request $request, $kdPengujian)
    {
        $dataPengujian = M_Pengujian::where('kd_pengujian', $kdPengujian) -> first();
        $dataMinConfidence = M_Nilai_Kombinasi::where('kd_pengujian', $kdPengujian) -> where('support', '>=', $dataPengujian -> min_confidence) -> get();
        $totalProduk = M_Produk::count();
        $dr = [
            'kdPengujian' => $kdPengujian,
            'dataPengujian' => $dataPengujian,
            'dataMinConfidence' => $dataMinConfidence,
            'totalProduk' => $totalProduk
        ];
        $pdf = PDF::loadview('main.apriori.cetakAnalisa', $dr);
        return $pdf -> stream();
    }

}
