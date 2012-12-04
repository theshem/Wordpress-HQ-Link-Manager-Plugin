Wordpress HQ Link Manager Plugin
================================

**A simple plugin Adds user's favorite attributes to the external links**

I'm not a wordpress developer, not even a wordpress lover! (not yet). I just wrote this plugin due to personal needs.

## Languages
The funny thing is that this plugin has its own language engine :D

* Make a folder based on your WP language (e.g. en\_US, fr\_FR) and place that in `language` folder.
* Make a copy of `lang.php` from 'language/fa_IR/' folder and paste that in yours.
* Edit `lang.php` and do what ever you want ;)

### Creating

Within `lang.php` file you will assign each line of text to an array called $lang with this prototype:

```PHP
$lang['language_key'] = "The actual message to be shown";
/* Example:
	$lang['page_title'] = "HQ Link Manager Settings";
*/
```

### Loading

Loading a line from `lang.php` file is done with the following code:

```PHP
$this->lang['language_key'];
/* Example:
	echo $this->lang['page_title'];
*/
```