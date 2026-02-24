<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Browse Routes
|--------------------------------------------------------------------------
|
| Routes for pages that don't require being logged in to view,
| specifically the information pages.
|
*/

/**************************************************************************************************
    Widgets
**************************************************************************************************/

Route::get('items/{id}', 'Users\InventoryController@getStack');
Route::get('pets/{id}', 'Users\PetController@getStack');
Route::get('{type}/{id}', 'Users\ArmouryController@getStack')->where(['type' => 'gear|weapons']);
Route::get('items/character/{id}', 'Users\InventoryController@getCharacterStack');

/**************************************************************************************************
    News
**************************************************************************************************/
// PROFILES
Route::group(['prefix' => 'news'], function () {
    Route::get('/', 'NewsController@getIndex');
    Route::get('{id}.{slug?}', 'NewsController@getNews');
    Route::get('{id}.', 'NewsController@getNews');
});

/**************************************************************************************************
    Sales
**************************************************************************************************/
// PROFILES
Route::group(['prefix' => 'sales'], function () {
    Route::get('/', 'SalesController@getIndex');
    Route::get('{id}.{slug?}', 'SalesController@getSales');
    Route::get('{id}.', 'SalesController@getSales');
});

/**************************************************************************************************
    Users
**************************************************************************************************/
Route::get('/users', 'BrowseController@getUsers');
Route::get('/blacklist', 'BrowseController@getBlacklist');
Route::get('/deactivated-list', 'BrowseController@getDeactivated');

// PROFILES
Route::group(['prefix' => 'user', 'namespace' => 'Users'], function () {
    Route::get('{name}/gallery', 'UserController@getUserGallery');
    Route::get('{name}/character-designs', 'UserController@getUserCharacterDesigns');
    Route::get('{name}/character-art', 'UserController@getUserCharacterArt');
    Route::get('{name}/favorites', 'UserController@getUserFavorites');
    Route::get('{name}/favorites/own-characters', 'UserController@getUserOwnCharacterFavorites');

    Route::get('{name}', 'UserController@getUser');
    Route::get('{name}/aliases', 'UserController@getUserAliases');
    Route::get('{name}/characters', 'UserController@getUserCharacters');
    Route::get('{name}/sublist/{key}', 'UserController@getUserSublist');
    Route::get('{name}/myos', 'UserController@getUserMyoSlots');
    Route::get('{name}/inventory', 'UserController@getUserInventory');
    Route::get('{name}/pets', 'UserController@getUserPets');
    Route::get('{name}/pets/{id}', 'UserController@getUserPet');
    Route::get('{name}/bank', 'UserController@getUserBank');
    Route::get('{name}/stats', 'UserController@getUserStats');
    Route::get('{name}/stats/logs/level', 'UserController@getUserLevelLogs');
    Route::get('{name}/stats/logs/stat-points', 'UserController@getUserStatLogs');
    Route::get('{name}/stats/logs/experience', 'UserController@getUserExperienceLogs');
    Route::get('{name}/armoury', 'UserController@getUserArmoury');

    Route::get('{name}/currency-logs', 'UserController@getUserCurrencyLogs');
    Route::get('{name}/item-logs', 'UserController@getUserItemLogs');
    Route::get('{name}/pet-logs', 'UserController@getUserPetLogs');
    Route::get('{name}/ownership', 'UserController@getUserOwnershipLogs');
    Route::get('{name}/submissions', 'UserController@getUserSubmissions');
    Route::get('{name}/gear-logs', 'UserController@getUserGearLogs');
    Route::get('{name}/weapon-logs', 'UserController@getUserWeaponLogs');
});

/**************************************************************************************************
    Characters
**************************************************************************************************/
Route::get('/masterlist', 'BrowseController@getCharacters');
Route::get('/myos', 'BrowseController@getMyos');
Route::get('/sublist/{key}', 'BrowseController@getSublist');
Route::group(['prefix' => 'character', 'namespace' => 'Characters'], function () {
    Route::get('{slug}', 'CharacterController@getCharacter');
    Route::get('{slug}/profile', 'CharacterController@getCharacterProfile');
    Route::get('{slug}/bank', 'CharacterController@getCharacterBank');
    Route::get('{slug}/level', 'CharacterController@getCharacterLevel');
    Route::get('{slug}/status-effects', 'CharacterController@getCharacterStatusEffects');
    Route::get('{slug}/inventory', 'CharacterController@getCharacterInventory');
    Route::get('{slug}/images', 'CharacterController@getCharacterImages');

    Route::get('{slug}/currency-logs', 'CharacterController@getCharacterCurrencyLogs');
    Route::get('{slug}/item-logs', 'CharacterController@getCharacterItemLogs');
    Route::get('{slug}/status-effect-logs', 'CharacterController@getCharacterStatusEffectLogs');
    Route::get('{slug}/ownership', 'CharacterController@getCharacterOwnershipLogs');
    Route::get('{slug}/change-log', 'CharacterController@getCharacterLogs');
    Route::get('{slug}/skill-logs', 'CharacterController@getCharacterSkillLogs');
    Route::get('{slug}/submissions', 'CharacterController@getCharacterSubmissions');
    Route::get('{slug}/stats/logs', 'CharacterController@getCharacterStatLogs');
    Route::get('{slug}/stats/logs/stat-points', 'CharacterController@getCharacterStatPointLogs');
    Route::get('{slug}/stats/logs/experience', 'CharacterController@getCharacterExperienceLogs');
    Route::get('{slug}/stats/logs/level', 'CharacterController@getCharacterLevelLogs');
    Route::get('{slug}/stats/logs/count', 'CharacterController@getCharacterCountLogs');

    Route::get('{slug}/gallery', 'CharacterController@getCharacterGallery');
    Route::get('{slug}/pets', 'CharacterController@getCharacterPets');
});
Route::group(['prefix' => 'myo', 'namespace' => 'Characters'], function () {
    Route::get('{id}', 'MyoController@getCharacter');
    Route::get('{id}/profile', 'MyoController@getCharacterProfile');
    Route::get('{id}/ownership', 'MyoController@getCharacterOwnershipLogs');
    Route::get('{id}/change-log', 'MyoController@getCharacterLogs');
});

/**************************************************************************************************
    World
**************************************************************************************************/

Route::group(['prefix' => 'world'], function () {
    Route::get('/', 'WorldController@getIndex');

    Route::get('currency-categories', 'WorldController@getCurrencyCategories');
    Route::get('currencies', 'WorldController@getCurrencies');
    Route::get('rarities', 'WorldController@getRarities');
    Route::get('species', 'WorldController@getSpecieses');
    Route::get('subtypes', 'WorldController@getSubtypes');
    Route::get('species/{id}/traits', 'WorldController@getSpeciesFeatures');
    Route::get('status-effects', 'WorldController@getStatusEffects');
    Route::get('subtypes/{id}/traits', 'WorldController@getSubtypeFeatures');
    Route::get('universaltraits', 'WorldController@getUniversalFeatures');
    Route::get('all-traits-index', 'WorldController@getKitchenSinkFeatures');
    Route::get('item-categories', 'WorldController@getItemCategories');
    Route::get('items', 'WorldController@getItems');
    Route::get('items/{id}', 'WorldController@getItem');
    Route::get('trait-categories', 'WorldController@getFeatureCategories');
    Route::get('traits', 'WorldController@getFeatures');
    Route::get('pet-categories', 'WorldController@getPetCategories');
    Route::get('pets', 'WorldController@getPets');
    Route::get('pets/{id}', 'WorldController@getPet');
    Route::get('traits/modal/{id}', 'WorldController@getFeatureDetail')->where(['id' => '[0-9]+']);
    Route::get('character-categories', 'WorldController@getCharacterCategories');
    Route::get('levels', 'WorldController@getLevels');
    Route::get('levels/{type}', 'WorldController@getLevelTypes');
    Route::get('stats', 'WorldController@getStats');
    Route::get('stats/{abbreviation}', 'WorldController@getStat');
    Route::get('weapon-categories', 'WorldController@getWeaponCategories');
    Route::get('weapons', 'WorldController@getWeapons');
    Route::get('weapons/{id}', 'WorldController@getWeapon');
    Route::get('gear-categories', 'WorldController@getGearCategories');
    Route::get('gear', 'WorldController@getGears');
    Route::get('gear/{id}', 'WorldController@getGear');
    Route::get('character-classes', 'WorldController@getCharacterClasses');
    Route::get('skill-categories', 'WorldController@getSkillCategories');
    Route::get('skills', 'WorldController@getSkills');
    Route::get('skills/{id}', 'WorldController@getSkill');
    Route::get('elements', 'WorldController@getElements');
    Route::get('elements/{id}', 'WorldController@getElement');
});

Route::group(['prefix' => 'prompts'], function () {
    Route::get('/', 'PromptsController@getIndex');
    Route::get('prompt-categories', 'PromptsController@getPromptCategories');
    Route::get('prompts', 'PromptsController@getPrompts');
    Route::get('{id}', 'PromptsController@getPrompt');
});

Route::group(['prefix' => 'shops'], function () {
    Route::get('/', 'ShopController@getIndex');
    Route::get('{id}', 'ShopController@getShop')->where(['id' => '[0-9]+']);
    Route::get('{id}/{stockId}', 'ShopController@getShopStock')->where(['id' => '[0-9]+', 'stockId' => '[0-9]+']);
});

/**************************************************************************************************
    Pet Drops
**************************************************************************************************/
Route::get('pets/pet/{id}', 'Users\PetController@getPetDrops');

/**************************************************************************************************
    Site Pages
**************************************************************************************************/
Route::get('credits', 'PageController@getCreditsPage');
Route::get('info/{key}', 'PageController@getPage');
Route::get('feeds', 'PageController@getFeedsPage');

/**************************************************************************************************
    Raffles
**************************************************************************************************/
Route::group(['prefix' => 'raffles'], function () {
    Route::get('/', 'RaffleController@getRaffleIndex');
    Route::get('view/{id}', 'RaffleController@getRaffleTickets');
});

/**************************************************************************************************
    Submissions
**************************************************************************************************/
Route::group(['prefix' => 'submissions', 'namespace' => 'Users'], function () {
    Route::get('view/{id}', 'SubmissionController@getSubmission');
});
Route::group(['prefix' => 'claims', 'namespace' => 'Users'], function () {
    Route::get('view/{id}', 'SubmissionController@getClaim');
});

/**************************************************************************************************
    Comments
**************************************************************************************************/
Route::get('comment/{id}', 'PermalinkController@getComment');
Route::get('sort-comments/{model}/{id}', 'Comments\CommentController@getSortedComments');

/**************************************************************************************************
    Galleries
**************************************************************************************************/
Route::group(['prefix' => 'gallery'], function () {
    Route::get('/', 'GalleryController@getGalleryIndex');
    Route::get('all', 'GalleryController@getAll');
    Route::get('{id}', 'GalleryController@getGallery');
    Route::get('view/{id}', 'GalleryController@getSubmission');
    Route::get('view/favorites/{id}', 'GalleryController@getSubmissionFavorites');
});

/**************************************************************************************************
    Reports
**************************************************************************************************/
Route::group(['prefix' => 'reports', 'namespace' => 'Users'], function () {
    Route::get('/bug-reports', 'ReportController@getBugIndex');
});

Route::get('time', function () {
    return date('Y-m-d H:i:s');
});
