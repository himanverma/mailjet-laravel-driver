<?php

use Mockery as m;
use Themsaid\MailPreview\PreviewTransport;

class MailPreviewTest extends TestCase
{
    public function testCreatesPreviewDirectory()
    {
        $message = new Swift_Message('Foo subject', '<html>Body</html>');
        $message->setFrom('myself@example.com');
        $message->setTo('me@example.com');
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $transport = new PreviewTransport(
            $files,
            'framework/emails'
        );

        $files->shouldReceive('exists')->once()->with('framework/emails')->andReturn(false);
        $files->shouldReceive('makeDirectory')->once()->with('framework/emails');
        $files->shouldReceive('put')->once()->with(
            'framework/emails/.gitignore',
            "*\n!.gitignore"
        );

        self::getMethod('createEmailPreviewDirectory')->invokeArgs($transport, []);
    }

    public function testCleansOldPreviews()
    {
        $message = new Swift_Message('Foo subject', '<html>Body</html>');
        $message->setFrom('myself@example.com');
        $message->setTo('me@example.com');
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $transport = new PreviewTransport(
            $files,
            'framework/emails',
            60
        );

        $files->shouldReceive('files')->once()->with('framework/emails')->andReturn(['path/to/old/file', 'path/to/new/file']);
        $files->shouldReceive('lastModified')->with('path/to/old/file')->andReturn(time() - 70);
        $files->shouldReceive('lastModified')->with('path/to/new/file')->andReturn(time());
        $files->shouldReceive('delete')->once()->with(['path/to/old/file']);

        self::getMethod('cleanOldPreviews')->invokeArgs($transport, [$message]);
    }

    public function testCreatesPreviewFiles()
    {
        $message = new Swift_Message('Foo subject', '<html>Body</html>', 'text/html');
        $message->setFrom('myself@example.com', 'Jack Black');
        $message->setTo('me@example.com');
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $transport = new PreviewTransport(
            $files,
            'framework/emails'
        );

        $files->shouldReceive('exists')->once()->with('framework/emails')->andReturn(true);
        $files->shouldReceive('files')->once()->with('framework/emails')->andReturn([]);

        $files->shouldReceive('put')->with(
            'framework/emails/'.$message->getDate()->getTimestamp().'_me_at_example_com_foo_subject.html',
            m::any()
        );

        $files->shouldReceive('put')->with(
            'framework/emails/'.$message->getDate()->getTimestamp().'_me_at_example_com_foo_subject.eml',
            $message->toString()
        );

        $transport->send($message);
    }

    /**
     * Gets a private method
     *
     * @param $name
     *
     * @return ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass(PreviewTransport::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
