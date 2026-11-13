<?php

use PHPUnit\Framework\TestCase;

/**
 * DE-11525 (audit #03): hookDisplayHeader() must not subscribe or unsubscribe
 * from the raw newsletter form request. Only the newsletter module hooks,
 * triggered after validation, may do that.
 */
class LegacyNewsletterPathTest extends TestCase
{
    private function hookDisplayHeaderSource()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/mailjet.php');
        $start = strpos($source, 'public function hookDisplayHeader()');
        $end = strpos($source, 'public function hookHeader()', $start);

        return substr($source, $start, $end - $start);
    }

    public function testDisplayHeaderDoesNotReadTheNewsletterForm()
    {
        $method = $this->hookDisplayHeaderSource();

        $this->assertStringNotContainsString("isSubmit('submitNewsletter')", $method);
        $this->assertStringNotContainsString('->subscribe(', $method);
        $this->assertStringNotContainsString('->remove(', $method);
    }
}
