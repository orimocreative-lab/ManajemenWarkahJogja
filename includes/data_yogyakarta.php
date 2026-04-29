<?php
/**
 * Data Kelurahan dan Kecamatan di Kota Yogyakarta (disesuaikan dengan lampiran)
 */

$yogyakarta_data = [
    'Tegalrejo' => ['Kricak', 'Karangwaru', 'Tegalrejo'],
    'Jetis' => ['Bumijo', 'Cokrodiningratan', 'Gowongan'],
    'Gedongtengen' => ['Pringgokusuman', 'Sosromenduran'],
    'Danurejan' => ['Bausasran', 'Suryatmajan', 'Tegalpanggung'],
    'Ngampilan' => ['Ngampilan', 'Notoprajan'],
    'Wirobrajan' => ['Pakuncen', 'Patangpuluhan', 'Wirobrajan'],
    'Mantrijeron' => ['Gedongkiwo', 'Mantrijeron', 'Suryodiningratan'],
    'Kraton' => ['Kadipaten', 'Panembahan', 'Patehan'],
    'Gondomanan' => ['Ngupasan', 'Prawirodirjan'],
    'Pakualaman' => ['Gunungketur', 'Purwokinanti'],
    'Gondokusuman' => ['Baciro', 'Demangan', 'Klitren', 'Terban'],
    'Umbulharjo' => ['Giwangan', 'Mujamuju', 'Pandeyan', 'Semaki', 'Sorosutan', 'Tahunan', 'Warungboto'],
    'Kotagede' => ['Purbayan', 'Prenggan', 'Rejowinangun'],
    'Mergangsan' => ['Brontokusuman', 'Keparakan', 'Wirogunan']
];

function getKecamatan() {
    global $yogyakarta_data;
    return array_keys($yogyakarta_data);
}

function getKelurahan($kecamatan) {
    global $yogyakarta_data;
    return isset($yogyakarta_data[$kecamatan]) ? $yogyakarta_data[$kecamatan] : [];
}
