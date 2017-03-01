<?php

/**
 * Unit tests for AuthN Request
 */
class Maestrano_Sso_UserTest extends PHPUnit_Framework_TestCase
{
    private $samlResp;
    private $subject;

    /**
     * Initializes the Test Suite
     */
    public function setUp()
    {
        $config = MaestranoTestHelper::getConfig();
        $marketplace = 'some-marketplace';
        Maestrano::with($marketplace)->configure($config['marketplaces'][0]);

        $this->samlResp = new SamlMnoRespStub();
        $this->subject = new Maestrano_Sso_User($this->samlResp);
    }

    public function testAttributeParsing()
    {
        $att = $this->samlResp->getAttributes();

        $this->assertEquals($att["mno_session"], $this->subject->getSsoSession());
        $this->assertEquals(new DateTime($att["mno_session_recheck"]), $this->subject->getSsoSessionRecheck());
        $this->assertEquals($att["group_uid"], $this->subject->getGroupUid());
        $this->assertEquals($att["group_role"], $this->subject->getGroupRole());
        $this->assertEquals($att["uid"], $this->subject->getUid());
        $this->assertEquals($att["virtual_uid"], $this->subject->getVirtualUid());
        $this->assertEquals($att["email"], $this->subject->getEmail());
        $this->assertEquals($att["virtual_email"], $this->subject->getVirtualEmail());
        $this->assertEquals($att["name"], $this->subject->getFirstName());
        $this->assertEquals($att["surname"], $this->subject->getLastName());
        $this->assertEquals($att["country"], $this->subject->getCountry());
        $this->assertEquals($att["company_name"], $this->subject->getCompanyName());
    }
}
