# Variable Inspector

Contributors: qriouslad  
Donate link: https://bowo.io/vi-sp-rdm  
Tags: php variables, variable dump, debug, developer  
Requires at least: 4.8  
Tested up to: 6.2.2  
Stable tag: 2.5.1  
Requires PHP: 5.6  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

![](.wordpress-org/banner-772x250.png)

Inspect PHP variables on a central dashboard in wp-admin for convenient debugging.

## Description

Variable Inspector allows you to easily inspect your PHP $variables in a visually clean manner at a central dashboard in wp-admin. It aims to be an **easy and useful enough dev and debug tool**. 

It provides **a single-line code** to inspect your variable (see "How to Use" below). Nothing is shown to site visitors nor being output on the frontend, and the **$variable content is nicely formatted for review** using [var_dump()](https://www.php.net/manual/en/function.var-dump.php), [var_export()](https://www.php.net/manual/en/function.var-export.php) and [print_r()](https://www.php.net/manual/en/function.print-r.php) on the inspector dashboard in wp-admin. 

It's a real time-saver for scenarios where [Xdebug](https://xdebug.org/) or even something like [Ray](https://myray.app/) is not ideal or simply an overkill. For example, when coding on a non-local environment via tools like [Code Snippets](https://wordpress.org/plugins/code-snippets/), [WPCodeBox](https://wpcodebox.com/), [Scripts Organizer](https://dplugins.com/products/scripts-organizer/) or [Advanced Scripts](https://www.cleanplugins.com/products/advanced-scripts/). Additionally, because it is a regular WordPress plugin, you simply install, activate and use without the need for complicated configuration.

### What Users Say

_"**Huge time-saver when working with PHP variables**."_ ~[Jeff Starr](https://digwp.com/2023/06/plugins-troubleshoot-debug-wordpress/)

_"**Works great!** Love this thing."_ ~[Josh](https://wordpress.org/support/topic/works-great-8269/)

_"**Every developer need it.** You can debug easily every variable."_ ~[Pexle Chris](https://wordpress.org/support/topic/awsome-plugin-that-every-developer-need-it/)

_"**All I need**. Enough to test the function of snippets. Works flawless."_ ~[@tesig](https://wordpress.org/support/topic/all-i-need-39/)

_"**It does what it does very well**, and doesn't try to do everything."_ ~[@swb1](https://wordpress.org/support/topic/exactly-what-i-needed-868/)

### How to Use

Simply place the following line anywhere in your code after the `$variable_name` you'd like to inspect:

`do_action( 'inspect', [ 'variable_name', $variable_name ] );`

If you'd like to record the originating PHP file and line number, append the PHP magic constants `__FILE__` and `__LINE__` as follows.

`do_action( 'inspect', [ 'variable_name', $variable_name, __FILE__, __LINE__ ] );`

This would help you locate and clean up the inspector lines once you're done debugging.

### Give Back

* [A nice review](https://bowo.io/vi-rvw-dsc) would be great!
* [Give feedback](https://bowo.io/vi-fdbk-dsc) and help improve future versions.
* [Github repo](https://bowo.io/vi-gthb-dsc) to contribute code.
* Sponsor my work with as little as USD 1 [monthly](https://bowo.io/vi-sp-gth-rdm) or [one-time](https://bowo.io/vi-sp-ppl-rdm).

### PRO Version

If you need something more versatile for larger, more complex projects, these [PRO features](https://bowo.io/vi-up-hstd-dsc) might come in handy:

- **vi( $variable )** inspector to replace the do_action() above and automatically includes the file path and line number. Or, use **vis( $variable_name, 'variable_name' )** when inspecting in/via code snippets plugins (Code Snippets, WPCodeBox, Scripts Organizer, etc.) or custom code module of page builders (Bricks, Oxygen Builder, etc.)
- **dump_print_r** and **dump_print_tree** viewers: both viewers combines var_dump and print_r, with the later allowing for collapsing and expanding data nodes, which is especially useful for inspecting long and complex arrays and objects.
- **kint** viewer: an advanced viewer with table view for multi-dimensional arrays, node access path info, capability to search inside variables, pop-up window viewing and advanced info for your complex objects, e.g. available methods and static class properties.
- **Categorize and filter results by color**. This, for example, allows you to categorize $variables according to which stage they're in inside your code.
- **Add counter or notes** to inspection results. Another way to tag $variables and provide context for the inspection results.
- **Search filter**: easily find certain $variables by name and type.
- **View up to 250 results**. The free version is limited to 25 results.
- **Individual and bulk deletion** of results. Easily clean up your inspection dashboard from obsolete results you no longer need to reference.

[Click here](https://bowo.io/vi-pro-scrsht-rdm) to see a screenshot of the [PRO version](https://bowo.io/vi-up-hstd-dsc) or see below in the screenshots section.

### What PRO Users Say

_"I was looking for a tool like this for a while. **Great solution!** Love this thing."_ ~Marco Frodl

_"I have used it for a couple of days and it has helped me a lot to **go faster when debugging**.""_ ~Javier Sor

_"I've only just started using it and it **really helps me get my head around the code of a plugin** I'm trying to customise"_ ~Tim Dickinson

_"I've been testing it and I really like it. It **helps a lot and saves a lot of time in development**. It's also compatible with Oxygen, Bricks, WPCodeBox, etc."_ ~Juan Jose Gonzalez, oxygenados.com

_"I just bought this! **Great plugin** - thank you for the hard work!"_ ~Tim Dickinson

_"I used to use Ray locally, I'm also using Xdebug a lot .. if I need to debug some variables, I use Query Monitor. I bought this, because **the UI is nice, it has all those good features from both worlds**."_ ~Peter Morvay

_"Worth the buy! **It is really good... great for debugging**. Specially, when you want to write custom code or understand some snippet ChatGPT gave you."_ ~John D

[Get the PRO version now!](https://bowo.io/vi-up-hstd-dsc)

### Check These Out Too

* [Admin and Site Enhancements](https://wordpress.org/plugins/admin-site-enhancements/) helps you to easily enhance various admin workflows and site aspects while replacing multiple plugins doing it.
* [System Dashboard](https://wordpress.org/plugins/system-dashboard/): Central dashboard to monitor various WordPress components, processes and data, including the server.
* [Debug Log Manager](https://wordpress.org/plugins/debug-log-manager/): Log PHP, database and JavaScript errors via WP_DEBUG with one click. Conveniently create, view, filter and clear the debug.log file.
* [Code Explorer](https://wordpress.org/plugins/code-explorer/): Fast directory explorer and file/code viewer with syntax highlighting.
* [Flexible Scroll Top](https://wordpress.org/plugins/flexible-scroll-top/) and [Floating Share Buttons](https://wordpress.org/plugins/floating-share-button/) is a lightweight combo with minimalist UI.
* [WordPress Newsboard](https://bowo.io/vi-wpn-dsc): The latest news, articles, podcasts and videos from 100+ WordPress-focused sources.

## Screenshots

1. The variable inspection dashboard
   ![The variable inspection dashboard](.wordpress-org/screenshot-1.png)
2. The PRO version
   ![The PRO version](.wordpress-org/screenshot-2.png)

## Frequently Asked Questions

### How was this plugin built?

Variable Inspector was built with: [WordPress Plugin Boilerplate](https://github.com/devinvinson/WordPress-Plugin-Boilerplate/) | [wppb.me](https://wppb.me/) | [Simple Accordion](https://codepen.io/gecugamo/pen/xGLyXe) | [Fomantic UI](https://fomantic-ui.com/). It was originally inspired by [WP Logger](https://wordpress.org/plugins/wp-data-logger/).

## Changelog

### Sponsor Variable Inspector

If this plugin has been useful for your **personal project(s), paid dev work, client site(s) and or agency's workflow**, please kindly consider **sponsoring from as little as USD 1** ([monthly](https://bowo.io/vi-sp-gth-chnlg) or [one-time](https://bowo.io/vi-sp-ppl-chnlg)). You can also choose to [upgrade to the PRO version](https://bowo.io/vi-up-chnlg). Thank you!

### 2.5.1 (2023.07.20)

* Fixed an issue where DB table creation failed when DB collation is empty/undefined for certain hosting / server set up.

### 2.5.0 (2023.07.05)

* Implement Source Sans Pro font for all viewers
* [PRO] Use semi-bold for keys to improve results readability

### 2.4.3.1 (2023.05.24)

* Fix JS syntax error rendering inspection dashboard disfunctional. Props to [@chillifish](https://wordpress.org/support/users/chillifish/) for [reporting it](https://wordpress.org/support/topic/none-of-the-links-in-admin-work/).

### 2.4.3 (2023.05.22)

* Add handling for null type / value
* Added link to changelog in footer
* [PRO] Feedback link in header no longer links to wordpress.org support forum. It now links to the contact form within wp-admin.
* [PRO] Fixed: space in vis() inspector's second parameter no longer causes JS error and prevents variable content from loading. This is handled by converting space to underscore, e.g. 'the variable' will show up as $the_variable in the inspection result accordion.
* [PRO] Fixed: special characters inside the 'notes' parameter in vi() and vis() inspectors are now handled better and won't cause JS error.
* [PRO] Fixed: JS error "Maximum call stack size exceeded" when performing ajax actions, e.g. generate sample results, refrehs, etc.
* [PRO] kint viewer: now will properly show $variable_name when getting the access path info of each node in the inspection result
* [PRO] Make the style of NULL results uniform across var_dump_r, var_dump_tree and kint viewers

### 2.4.0 (2023.05.15)

* [PRO] Added vis() inspector for inspecting $variables via code snippets plugins or custom code modules of page builders
* Remove freemius SDK from free version

### 2.3.0 (2023.04.24)

* Add identification and labeling of 'float' / 'double' variable type
* Add results counter. Shown next to the 'Results' heading.
* Added modal window for sponsorship
* Launch of PRO version. To find out and/or upgrade, simply click on the green 'Upgrade' button in the inspection dashboard. Lifetime license is available.

### 2.2.1 (2023.04.16)

* Improve escaping of variables with string type. Preventing inspector layout / HTML output interferrence, especially when the string contains HTML tags.

### 2.2.0 (2023.04.13)

* Clean up and polish inspector dashboard UI. Remove separator lines.
* Update shortlinks on the dashboard.

### 2.1.0 (2023.04.11)

* Limit height of each result's content and add scroll bar when content is taller than the limit. This should prevent very long result content from occupying the screen.
* Change background color of each result's content to white for better readability.

### 2.0.0 (2023.04.10)

* Add button to easily generate sample results. Useful for first-time installation.
* Overall UI/X polish which includes adding loading animation on button clicks, improved auto-refresh UI, improved readability of variable type labels and ensure preferred/chosen viewer is effective/respected after varioius clicks / interaction with the inspection dashboard.
* Change 'Donate' (PayPal) link to [(Github) 'Sponsor'](https://bowo.io/sponsor-vi) link. Do consider sponsoring the continued development of Variable Inspector.
* Add link to [WordPress Newsboard](https://bowo.io/wpn-vi) in footer, a WordPress news aggregator site I've maintained since 2014.
* Integrate Freemius SDK in preparation for Pro version.

### 1.9.0 (2023.04.04)

* Move storage of preferred inspection method from wp_options to user meta.

### 1.8.0 (2022.12.08)

* Fix an issue where sometimes inspection shows empty or false result when result type is array or object.
* Additional suppression of admin notices via all_admin_notices hook and via CSS.

### 1.7.1 (2022.10.25)

* All admin notices are now suppressed, i.e. no longer shown, on the Variable Inspector page.

### 1.7.0 (2022.10.11)

* Add viewer (function) selector, e.g. print_r, that will apply to all inspection results after the selection is made and will persist after page reload. The selection is stored in wp_options table. Different viewer can still be selected for each result. Props to [@pexlechris](https://profiles.wordpress.org/pexlechris/) for [the feedback](https://wordpress.org/support/topic/awsome-plugin-that-every-developer-need-it/).

### 1.6.0 (2022.10.11)

* Add toggle to expand or collapse all inspection results. Props to [@pexlechris](https://profiles.wordpress.org/pexlechris/) for [the feedback](https://wordpress.org/support/topic/awsome-plugin-that-every-developer-need-it/).

### 1.5.0 (2022.10.09)

* Remove CodeStar framework dependency and replace with lightweight solution
* Dequeue public css and js files as they are empty and unused

### 1.4.0 (2022.08.18)

* Add Refresh button and "Auto refresh" checkbox to load latest results. Props to [@imantsk](https://github.com/imantsk) for the [code and suggestion](https://github.com/qriouslad/variable-inspector/issues/3)
* Add quick tutorial on the inspector results page to enable users to quickly reference the inspector code

### 1.3.2 (2022.05.26)

* Confirmed compatibility with WordPress 6.0

### 1.3.1 (2022.05.19)

* Fixed output via var_export()
* Better sanitization of variable name output
* Update plugin description

### 1.2.0 (2022.04.14)

* Fixed output buffering mistake causing the output of the '1' character in variable values
* NEW: implement tabbed output of var_export, var_dump and print_r

### 1.1.0 (2022.04.13)

* Fixed "Fatal error: Uncaught Error: Call to undefined function dbDelta()". Thanks to [@rashedul007](https://profiles.wordpress.org/rashedul007/) for [the fix](https://github.com/qriouslad/variable-inspector/pull/2)!

### 1.0.1 (2022.04.13)

* Initial stable release

## Upgrade Notice

None required yet.