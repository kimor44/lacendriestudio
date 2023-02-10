
# La Cendrie Studio

<p align="center"><img src="https://github.com/kimor44/lacendriestudio/blob/main/wp-content/themes/lacendriestudio/assets/images/logo_la_cendrie.jpg"></p>

## Table of Contents
- [La Cendrie Studio](#la-cendrie-studio)
    - [Table of Contents](#table-of-contents)
    - [General Info](#general-info)
    - [Technologies](#technologies)
    - [Prerequisite](#prerequisite)
    - [Installation](#installation)
    - [Settings](#settings)
    - [Plugins](#plugins)
        - [TW Sliders](#tw-sliders)
    - [Must Use plugins](#must-use-plugins)
        - [Filter end time slot](#filter-end-time-slot)
## General info

La cendrie studio is a private rehearsal room where you can book slots for your music group
## Technologies

[![WordPress - 6.1.1](https://img.shields.io/static/v1?label=WordPress&message=6.1.1&color=%2321759B)](https://wordpress.com/fr/)
[![PHP - 7.4.32](https://img.shields.io/static/v1?label=PHP&message=7.4.32&color=%23777BB4)](https://www.php.net/)
[![TailwindCSS - ^3.0.23](https://img.shields.io/static/v1?label=TailwindCSS&message=^3.0.23&color=06B6D4)](https://tailwindcss.com/)
[![issues - lacendriestudio](https://img.shields.io/github/issues/kimor44/lacendriestudio)](https://github.com/kimor44/lacendriestudio/issues)

dynamic TailwindCSS 

[![TailwindCSS](https://img.shields.io/badge/dynamic/json?label=TailwindCSS&query=%24._devDependencies.tailwindcss&url=https%3A%2F%2Fgithub.com%2Fkimor44%2Flacendriestudio%2Fblob%2Fmain%2Fwp-content%2Fthemes%2Flacendriestudio%2Fpackage.json)](https://github.com/kimor44/lacendriestudio/blob/main/wp-content/themes/lacendriestudio/package.json)
## Prerequisite

Before installation, make sure you have a web development plateform already installed in your local machine like :

* [MAMP](https://www.mamp.info/en/downloads/) for macOS
* [WAMP](https://www.wampserver.com/) for Windows
* [LAMP](https://doc.ubuntu-fr.org/lamp) for Linux

Be sure you have PHP and MySQL installed.

Create a database (e.g. lacendriestudio_db) with your web development plateform (MAMP, WAMP or LAMP).
## Installation

Install La Cendrie Studio project by cloning this repository

```bash
  git clone https://github.com/kimor44/lacendriestudio.git
  cd lacendriestudio
```

Run your PHP server (you can make this with your web development plateform :smirk:)

You should come across a page that explains the different parameters that you will have to enter to configure your WordPress.

Let's me explain you this while clicking on the `Let's go !` button.

You will be prompted to enter those parameters :

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `database name` | `your_database_name` | The name of your database e.g. "lacendriestudio_db" like seen above |
| `login` | `your_db_login` | Your DB login. Usually `root` on the local DB |
| `password` | `***********` | Your DB password. Usually `root` on the local DB |
| `Database address` | `localhost` | Your localhost |
| `Table prefix` | `wp_` | default value `wp_`. this will prefixe all your tables. You can put the prefixe you want |

Once you have entered all the fields correctly, click on the "Send" button.

You should arrive on a page that tells you that you have passed the first part of the installation.

Then, click on the `Launch the installation` button.

The last step is the "Required information" page.

You will prompted to enter those informations :

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Site title` | `your site title` | The title of your site you want |
| `login` | `your login` | Your login. This is the one that will appear in the administration section, the comments... |
| `password` | `***********` | The admin password you want |
| `Your email` | `your@email.com` | Enter your email adress |
| `Visibility by search engines` | `checkbox` | Check it to ask search engines not to index this site |

Once you have entered all the fields correctly, click on the `Install WordPress` button.

#### What a success ! :tada:

This the last page before you start the experience with that theme. It summarizes the name of your username and your password (fortunately not revealed).

Click on the `login` button, then enter your login/email and your password and you will arrive on the landing page of the site. :tada:
## Settings

WordPress offers plenty of parameter tweaks. Don't hesitate to explore the `appearance` and the `settings` sections in the admin panel.

First, you need to activate the right theme.

Go to `Appearance` > `Themes` in the admin pannel, choose the `La Cendrie Studio` theme and activate it.

Then you can also change the background color and set it to black which is the recommended color.

To do this, go to `Appearance` > `Customize` > `Color` > `Select color`, choose the black (or the color you want) and validate by clicking on the `publish` button.

You can set many parameters like this.
## Plugins

### TW Sliders

Plugin developed for the needs of the sliders on the home page.

This allows for lightweight code for simple functionality.

Feel free to add or improve features.
## Must Use plugins

### Filter end time slot

This is a replacement for the end time slot in the webba-booking plugin when the summary of reserved time slots appears before validation.

The client needs to define different slot durations depending on the time of day.

Need to do the same manipulation in other parts of the plugin.