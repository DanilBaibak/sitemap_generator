# [Sitemap generator](http://georgeosddev.github.com/markdown-edit)

The application for generating sitemap in the xml format. 

### The list of the additional features:
* Depth of scan:
  * Only current page
  * All pages
* Last modification:
  * None
  * Use server's response
* Rung of the priority:
  * None
  * Calculate automatically
* Change frequency:
  * None
  * Hourly
  * Daily
  * Monthly
  * Yearly

### Install:
#### Download Sources:
use git

```bash
git clone https://github.com/DanilBaibak/sitemap_generator.git
```

#### Deploy to some web server:
```bash
cp config/config_sample.php config/config.php
```
In the config file ```config/config.php``` change varibale ```siteUrl``` to the you url or IP.

```bash
cp server/config_sample.js server/config.js
```
In the config file ```server/config.js``` change varibale ```host``` to the you url or IP.
```bash
bash install.sh
```
All dependencies will be automatically installed and started [pm2](http://pm2.keymetrics.io/) for [nodejs](https://nodejs.org/en/)

