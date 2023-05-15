# Lorekeeper

Lorekeeper is a framework for managing deviantART-based ARPGs/closed species masterlists coded using the Laravel framework. In simple terms - you will be able to make a copy of the site, do some minor setup/enter data about your species and game, and it'll provide you with the automation to keep track of your species, players and ARPG submissions.

- Demo site: [http://lorekeeper.me/](http://lorekeeper.me/)
- Wiki: [http://wiki.lorekeeper.me](http://wiki.lorekeeper.me/index.php?title=Main_Page)

# Info

- Users can create an account which will hold their characters and earnings from participating in the game.
- Mods can add characters to the masterlist, which can also record updates to a character's design. (Yes, multiple mods can work on the masterlist at the same time.)
- Characters get a little bio section on their profile that their owners can edit. Personalisation!
- Users' ownership histories (including whether they are an FTO) and characters' previous owners are tracked.
- Users can submit art to the submission queue, which mods can approve/reject. This dispenses rewards automagically.
- Users can spend their hard-earned rewards immediately, without requiring mods to look over their trackers (because it's all been pre-approved).
- Characters, items and currency can be transferred between users. Plus...secure trading between users for game items/currency/characters on-site is also a thing.
- Logs for all transfers are kept, so it's easy to check where everything went. 
- The masterlist is king, so ownership can't be ambiguous, and the current design of a character is always easily accessible.
- Speaking of which, you can search for characters based on traits, rarity, etc. Also, trait/item/etc. data get their own searchable lists - no need to create additional pages detailing restrictions on how a trait should be drawn/described.
- Unless you want to, in which case you can add custom pages in HTML without touching the codebase!
- A raffle roller for consecutive raffles! Mods can add/remove tickets and users who have already won something will be automatically removed from future raffles in the sequence.
- ...and more! Please refer to the [Wiki](http://wiki.lorekeeper.me/index.php?title=Category:Documentation) for more information and instructions for usage.

## inventory_stacks

Important: For those who are not familiar with web dev, please refer to the [Wiki](http://wiki.lorekeeper.me/index.php?title=Tutorial:_Setting_Up) for a much more detailed set of instructions!!

This changes the default inventory in Lorekeeper from displaying each user_item row as a stack of 1, and instead condenses duplicate entries into stacks. This has affected Inventory, Trades, and Design Updates the most, though there could still be remnants of code that still aren't using the new system.

Once the changes are pulled, the database needs to be updated as well - this can be done with:

```
$ git clone https://github.com/corowne/lorekeeper.git
```

## Configure .env in the directory

```
$ cp .env.example .env
```

Client ID and secret for at least one supported social media platform are required for this step. See [the Wiki](http://wiki.lorekeeper.me/index.php?title=Category:Social_Media_Authentication) for platform-specific instructions.

Add the following to .env, filling them in as required (also fill in the rest of .env where relevant):
```
CONTACT_ADDRESS=(contact email address)
DEVIANTART_ACCOUNT=(username of ARPG group account)
```

## Setting up

Composer install:
```
$ composer install
```

Generate app key and run database migrations:
```
$ php artisan key:generate 
$ php artisan migrate
```

The migrations will add 2 new columns to user_items: trade_count and update_count, for tracking items held in trades and updates respectively. It will also change the default value of count in user_items to 0.

Note that existing data in the database will need to be edited such that duplicate entries (where the item_id, user_id, and data are the same) need to be combined separately.

You will need to send yourself the verification email and then link your social media account as prompted.

You can also delete the duplicate rows once the count column is updated. However, this will probably require deleting the item logs associated with the affected stacks, unless you create your own workaround.

If you have any questions, please feel free to ask in the Discord server: https://discord.gg/U4JZfsu

## embed_service

This adds the EmbedController and EmbedService, which makes use of [oscarotero/Embed](https://github.com/oscarotero/Embed) library.

You will need to install the above library and have at least one of [these PSR-7 libraries](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations). The composer.json has already been updated to include these libraries, so if you don't want to customise, just run `composer update` after pulling the branch.

### How to use

For server-side queries, add the EmbedService to the target file. Create an instance of the service to call getEmbed(), which only takes one argument: an URL. It will return an OEmbed response if it finds one. The library is able to return a variety of different responses, so don't be afraid to read up the documentation and change it to suit your needs!

For client-side queries, you can use jQuery's get() function to query the controller, which will handle the communication between the client and service. The controller also does validation to ensure that the input is actually in a URL format, and is from an accepted domain. 
Currently, it only accepts dA URLs, but can be have other sites added, or just have that part of the validation removed entirely.
The controller will also process the response to return only the image URL and thumbnail URL - you can configure these to your needs as necessary. 