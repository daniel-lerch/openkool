# Installation #

### todo ###

* Integrate behat tests into PHPStorm
* Configure Selenium2 support locally
* install on Zeta (including phantomjs)
* +hook on Git commit w/ mailing

composer update

### Phantomjs (headless browser) ###

```
apt purge phantomjs
wget https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-linux-x86_64.tar.bz2
tar xvjf phantomjs-2.1.1-linux-x86_64.tar.bz2
mv /path/to/phantom/untar/bin/phantomjs /usr/bin/
```

### Selenium (real browser testing) ###
optional and not tested: selenium browser tests
- Download latest Selenium jar from the: http://seleniumhq.org/download/
 Run Selenium2 jar before your test suites (you can start this proxy during system startup):
```
java -jar selenium-server-*.jar
```

### Additional configuration ###

Because kOOL is ISO-8859-1, but behat uses UTF-8, we need to set all files in /tests to UTF-8.
For PHPStorm, this can be set in File->Settings->Editor->File-Encoding.

# Important Notice #

Every Scenario in a Feature will create a new Session within Mink. So when we start a Scenario to do something within kOOL, we need to login as admin or some other user. To define this Step for the whole Feature, we make use of "Background".

See: http://behat.org/en/latest/user_guide/writing_scenarios.html#backgrounds


--------------------------

Problem in step "i follow " to click on a link, because normally a link to a subpage is hidden in folded menu.

# Usage #

```
cd tests
phantomjs --webdriver=8643 #run phantomjs
../vendor/bin/behat -p phantomjs --tags '~@notesting' --format pretty
```

### Customize Output formatters ###
Per default the output will be directly printed in shell. We the extension emuse/behat-html-formatter it is possible to create reports in html-files.
```
../vendor/bin/behat -p phantomjs --format html
```


### Running only specific scenarios and/or features ###
A single feature:
bin/behat features/filename.feature

A single scenario within a feature:
bin/behat features/filename.feature:7 where 7 represents the first line of the scenario. Exclude the tagname if present.

All tests with a certain tag (using @ symbol)
bin/behat --tags @tagname

If a test fails because of a known bug, which has a ticket and will be fixed in near future, the tag "@notesting" can be added to the scenario / feature to skip the test. **Important: Add info about the deactivated test to the ticket**


### Use XDEBUG in phpstorm ###
Before executing behat, type "export XDEBUG_CONFIG="idekey=PHPSTORM"" in the console and ensure that xdebug is enabled in php-cli.


### Additional links ###
How behat works - Browser emulation http://docs.behat.org/en/v2.5/cookbook/behat_and_mink.html
tipps: https://www.tentacode.net/10-tips-with-behat-and-mink