# moodle-tool_question_reducer

A admin tool whos goal is to reduce the number of questions within a site through removing redundant questions. There a few different ways it attempts to acheive this:

* Remove duplicate questions within question categories
* Merges similar question category with a context together.
* Removes unlinked questions

## Tests

Run with this command:

```php
./vendor/bin/phpunit -c admin/tool/question_reducer/phpunit.xml
```