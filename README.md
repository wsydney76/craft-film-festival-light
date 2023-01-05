# Film Festival Light

Adds film festival functionality to Craft Starter.

__Work in progress. This is just a proof of concept, extracting basic things from Festival Starter.__

The goal is to decouple specific information models and templates from a basic starter project.

## Information model

Overview of sections and fields installed by the plugin:

The sections use generic fields from the starter like Featured Image, Tagline, Body Content etc.

The templates reuse layout elements from the starter, like cards and widgets.

### Film

* Original title
* Film poster
* Sections/Competitions/Topics/Sponsors
* FSK
* Runtime
* Release year
* Genres/countries/
* Language/subtitle languages
* Cast/script/camera/director

### Person

* First name/name affix/last name
* Photo (portrait)
* Birthday
* Short bio
* Filmography

### Location

* Address
* Phone number
* Email

### Screening

* Films
* Location
* Screening date/time

### Section

### Competition

* Jury

Basically the same as a section, but add a `Jury` field. Enter award winners/nominees as a text block.

### Sponsor

Use featured image for sponsor logo.

### Diary

* Diary date
* Locations/films/persons

Add a gallery block for photos.

### Topic/Country/Genre/Language

Taxonomies.

## Requirements

This plugin requires Craft CMS 4.3.5 or later, and PHP 8.0.2 or later.

Latest version of [Craft 4 Starter](https://github.com/wsydney76/craft4-ddev-starter) must be installed.
 

## Installation

Update `composer.json`

```json
{
  "minimum-stability": "dev",
  "prefer-stable": true,
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/wsydney76/craft-film-festival-light"
    }
  ]
}
```

Open your terminal and run the following commands:


```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require wsydney76/craft-film-festival-light

# tell Craft to install the plugin
./craft plugin/install ff-light
```

## Getting Started

tbd.

### Initialize

Run `craft ff-light/install`

This will

* add new sections and fields.
* add page entries.
* provide generic twig templates.


Run `craft ff-light/install/seed` to create some (very) basic example content. Be sure there are some wide images.

### CSS

Add the plugins template directory to `tailwind.config.js`:

```javascript
 content: [
    // ...
    './vendor/wsydney76/craft-film-festival-light/src/**/*.twig',
    // ...
],
```

Run `npm run build`.

### Navigation

New pages are all added to the first level of the main navigation. So visit the `page` entries index and rearrange the pages hierarchy as you like.


### Custom config

Update the `searchSections` settings and include the sections that should be included in the search:

```php
'searchSections' => ['news', 'page', 'legal', 'film', 'person', 'filmSection', 'competition', 'topic', 'diary'],
```

Update the `sectionsRoots` setting like this:

```php
 'sectionRoots' => [
        '_sections',
        '@ff/_sections'
    ],
```

This will allow the starter to find section specific templates (card content, json-ld) provided by the plugin.

Update the `sitemapSections` settings and include the sections you want to appear on the sitemap, e.g..:

```php
'sitemapSections' => [ 
    ['handle' => 'competition', 'orderBy' => 'title'],
    ['handle' => 'location', 'orderBy' => 'title'],
   ...
]
```

## Home page

The plugin does not touch your home page.

For a quick start, add a couple of `Featured Entries` to your home page (e.g. diaries/sections) and add this to your template:

```twig
{% include '@ff/_blocks/home.twig' %}
```

Create a hero area that may point to the program page.

## Templating

There are two ways to use your own templates:

### Replace single templates

* Create a folder named `_ff` below your projects template directory.
* Create templates that mirror path and filename of the template to be replaced, e.g. `_ff/_partials/screening.twig`

### Completely

* Copy the plugins templates folder somewhere into your projects template directory.
* Adjust the section sections to point to your own templates.
* Goto the plugins setting page and enter your new root.
* Or disable/uninstall the plugin.

## Updates

There is currently no migration functionality.

Updating the plugin and running `craft ff-light/install` will not touch existing sections/fields, but will simply add new ones and update the twig templates.

This means you have to update field layouts manually in order to add any new fields.

