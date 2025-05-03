<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Governorate;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governorates = Governorate::pluck('id', 'name');

        // مناطق غزة
        if (isset($governorates['Gaza'])) {
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Rimal North']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Rimal South']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Shuja\'iyya']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Zeitoun']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Sabra']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Daraj']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Tuffah']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Sheikh Radwan']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Sheikh Ajlin']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Nasr']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Yarmouk']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Tel al-Hawa']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Saraya']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Sahaba']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Universities']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Beach Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Mughraqa']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Zawaida']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Zahra City']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Sawafir Eastern']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Sawafir Western']);
            Area::firstOrCreate(['governorate_id' => $governorates['Gaza'], 'name' => 'Qubba']); // تم إزالة المسافة
        }

        // مثال لمناطق رفح
        if (isset($governorates['Rafah'])) {
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Al-Shaboura Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Western Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Yibna Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Badr Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Canada Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Saudi Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Al-Brazil']);         // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Al-Jeneina']);       // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Khirbet al-Adas']);  // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Al-Salam']);         // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Al-Za\'raba Neighborhood']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Al-Hashash Neighborhood']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Thal al-Sultan Neighborhood']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Shokat as-Sufi Village']);
            Area::firstOrCreate(['governorate_id' => $governorates['Rafah'], 'name' => 'Umm al-Kilab Village']);
        }

        // مثال لمناطق خانيونس
        if (isset($governorates['Khan Younis'])) {
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Khan Yunis Camp']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Khan Yunis City']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Abasan al-Kabira']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Bani Suheila']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Abasan al-Saghira']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Khuza\'a']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Qarara']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Qizan an-Najjar']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Fukhari']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Qa\' al-Kharaba']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Qa\' al-Qurein']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Umm Kameil']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Umm al-Kilab']);
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Mu\'askar']);        // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Satar al-Gharbi']); // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Satar al-Sharqi']); // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Mawasi']);         // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['Khan Younis'], 'name' => 'Al-Sheikh Nasser Neighborhood']);
        }

        // مثال لمناطق الوسطى
        // **** تم تصحيح المفتاح هنا ****
        if (isset($governorates['Middle Area'])) {
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Nuseirat Camp']); // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Bureij Camp']);   // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Maghazi Camp']);  // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Deir al-Balah Camp']);// تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Deir al-Balah City']); // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Zawaida']);       // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Musaddar']);      // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Wadi al-Salqa']);    // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Mughraqa']);      // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Nuba']);          // تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Souq Neighborhood']);// تم تصحيح المفتاح
            Area::firstOrCreate(['governorate_id' => $governorates['Middle Area'], 'name' => 'Al-Bahr Neighborhood']);// تم تصحيح المفتاح
        }

        // مثال لمناطق شمال غزة
        if (isset($governorates['North Gaza'])) {
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Jabalia al-Balad']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Beit Lahia']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Beit Hanoun']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Umm al-Nasr']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Jabalia al-Nazla']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Izbat Abd Rabbo']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Izbat Beit Hanoun']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Al-Atatra']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Jabalia al-Mu\'askar']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Beit Lahia Project']);    // تم إزالة المسافة
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Al-Karama Neighborhood']);
            Area::firstOrCreate(['governorate_id' => $governorates['North Gaza'], 'name' => 'Abd al-Rahman Neighborhood']);
        }
    }
}