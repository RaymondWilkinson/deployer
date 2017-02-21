<?php

namespace REBELinBLUE\Deployer\Tests\Unit\Notifications\Configurable;

use Mockery as m;
use REBELinBLUE\Deployer\Deployment;
use REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFailed;
use REBELinBLUE\Deployer\Notifications\Notification;
use REBELinBLUE\Deployer\Project;

/**
 * @coversDefaultClass \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFailed
 */
class DeploymentFailedTest extends DeploymentFinishedTestCase
{
    /**
     * @covers ::__construct
     */
    public function testExtendsNotification()
    {
        $project    = m::mock(Project::class);
        $deployment = m::mock(Deployment::class);

        $notification = new DeploymentFailed($project, $deployment);

        $this->assertInstanceOf(Notification::class, $notification);
    }

    /**
     * @covers ::toTwilio
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildTwilioMessage
     */
    public function testToTwilio()
    {
        $this->toTwilio(DeploymentFailed::class, 'deployments.failed_sms_message');
    }

    /**
     * @covers ::toWebhook
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildWebhookMessage
     */
    public function testToWebhook()
    {
        $this->toWebhook(DeploymentFailed::class, 'failure', 'deployment_failed');
    }

    /**
     * @covers ::toMail
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildMailMessage
     */
    public function testToMail()
    {
        $this->toMail(
            DeploymentFailed::class,
            'deployments.failed_email_subject',
            'deployments.failed_email_message',
            'error'
        );
    }

    /**
     * @covers ::toMail
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildMailMessage
     */
    public function testToMailWithReason()
    {
        $this->toMail(
            DeploymentFailed::class,
            'deployments.failed_email_subject',
            'deployments.failed_email_message',
            'error',
            true
        );
    }

    /*
     * @covers ::toSlack
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildSlackMessage
     */
    public function testToSlack()
    {
        $this->toSlack(DeploymentFailed::class, 'deployments.failed_slack_message', 'error');
    }

    /*
     * @covers ::toSlack
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildSlackMessage
     */
    public function testToSlackWithCommitUrl()
    {
        $this->toSlack(DeploymentFailed::class, 'deployments.failed_slack_message', 'error', false);
    }

    /**
     * @covers ::toHipchat
     * @covers \REBELinBLUE\Deployer\Notifications\Configurable\DeploymentFinished::buildHipchatMessage
     */
    public function testToHipchat()
    {
        $this->toHipchat(DeploymentFailed::class, 'deployments.failed_hipchat_message', 'error');
    }
}
