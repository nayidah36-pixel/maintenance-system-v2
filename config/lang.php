<?php
// Default to English if no session preference parameter is assigned
$lang = isset($_SESSION['selected_lang']) ? $_SESSION['selected_lang'] : 'en';

$translations = [
    'en' => [
        'dashboard' => 'Dashboard Overview',
        'file_repair' => 'File New Repair',
        'my_logs' => 'My Request Logs',
        'logout' => 'Logout Account',
        'welcome' => 'Welcome',
        'estate' => 'Central Residential Estate Complex',
        'update_profile' => 'Update Profile',
        'change_password' => 'Change Password',
        'lang_selection' => 'Language Selection',
        'support' => 'Helpdesk & Support',
        'sign_out' => 'Sign Out System',
        'headline' => 'Initiate Specialized Maintenance Dispatch',
        'electrician' => 'Electrician Services',
        'electrician_desc' => 'Grid systems & fault checks',
        'plumbing' => 'Plumbing & Pipelines',
        'plumbing_desc' => 'Leak monitoring & structural pipes',
        'painting' => 'Wall Painting & Finishes',
        'painting_desc' => 'Surface preservation & coats',
        'carpentry' => 'Carpentry & Framework',
        'carpentry_desc' => 'Fittings, doors & interior mounts',
        'appliance' => 'Appliance Mechanical Fixes',
        'appliance_desc' => 'Hardware restoration & optimization',
        'solar' => 'Solar Tech Grid Units',
        'solar_desc' => 'Inverters & rooftop system arrays'
    ],
    'sw' => [
        'dashboard' => 'Mazingira ya Kazi',
        'file_repair' => 'Ripoti Itilafu Mpya',
        'my_logs' => 'Kumbukumbu Zangu',
        'logout' => 'Ondoka Kwenye Mfumo',
        'welcome' => 'Karibu',
        'estate' => 'Eneo Kuu la Makazi ya Kitongoji',
        'update_profile' => 'Rekebisha Wasifu',
        'change_password' => 'Badilisha Nywila',
        'lang_selection' => 'Chagua Lugha',
        'support' => 'Msaada na Huduma',
        'sign_out' => 'Ondoka Mfumoni',
        'headline' => 'Anzisha Mgawo Maalum wa Matengenezo',
        'electrician' => 'Huduma za Stima',
        'electrician_desc' => 'Mifumo ya umeme na ukaguzi wa hitilafu',
        'plumbing' => 'Mifumo ya Mabomba ya Maji',
        'plumbing_desc' => 'Kufuatilia uvujaji na mabomba ya muundo',
        'painting' => 'Kupaka Rangi na Kumalizia',
        'painting_desc' => 'Uhifadhi wa nyuso na tabaka za rangi',
        'carpentry' => 'Useremala na Miundo',
        'carpentry_desc' => 'Vifaa, milango na miundo ya ndani',
        'appliance' => 'Matengenezo ya Vyombo vya Nyumbani',
        'appliance_desc' => 'Urekebishaji wa vifaa na uboreshaji',
        'solar' => 'Mifumo ya Umeme wa Jua',
        'solar_desc' => 'Vigeuzi vya umeme na paneli za paa'
    ]
];

// Helper shortcut function for clean theme rendering inside dashboard pages
function __($key) {
    global $translations, $lang;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $key;
}
?>