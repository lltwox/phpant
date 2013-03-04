PHPAnt
======

PHPAnt is a simple tool, allowing you to replace [Apache Ant](http://ant.apache.org/) build.xml
files with PHP code.

## How to use ##

The idea behind PHPAnt is very simple and can be seen on the example below.

This is how sample build.xml file from
[Wikipedia](http://en.wikipedia.org/wiki/Apache_Ant#Sample_build.xml_file) looks
when using PHPAnt:
```php
<?php
$ant = new PHPAnt\Ant('Hello', 'compile');
$ant->target(
    array('name' => 'clean', 'description' => 'remove intermediate files'),
    function($ant) {
        $ant->delete(array('dir' => 'classes'));
    }
);
$ant->target(
    array('name' => 'clobber', 'depends' => 'clean', 'description' => 'remove all artifact files'),
    function($ant) {
        $ant->delete(array('file' => 'hello.jar'));
    }
);
$ant->target(
    array('name' => 'compile', 'description' => 'compile the Java source code to class files'),
    function($ant) {
        $ant->mkdir(array('dir' => 'classes'));
        $ant->javac(array('srcdir' => '.', 'destdir' => 'classes'));
    }
);
$ant->target(
    array('name' => 'jar', 'depends' => 'compile', 'description' => 'create a Jar file for the application'),
    function($ant) {
        $ant->jar(
            array('destfile' => 'hello.jar'),
            function ($ant) {
                $ant->fileset(array('dir' => 'classes', 'includes' => '**/*.class'));
                $ant->manifest(array(),
                    function($ant) {
                        $ant->attribute(array('name' => 'Main-Class', 'value' => 'HelloProgram'));
                    }
                );
            }
        );
    }
);
$ant->run();
```

Builder object provides `save(string $filename = 'build.xml')` method to save xml file for later use
and `run(string $target)` method for xml file to be execute immediately.

### How is it better, than normal XML files? ###

In several ways:
* When using PHPAnt the idea behind build.xml changes. This is not a file, describing
how to build current project in any scenario possible, but a file describing how to do it in
current environment, with current fileset and for only specific task. PHP is better suited to
make these kind of decisions.
* Using PHP open doors to all kinds of xml-nodes reuse - between targets or even projects.
* There is no need to write and mantain XML files.

### Why not use Phing instead? ###

Most CI servers come with Ant pre-installed. That means that you get comprehensive
list of tasks out of the box in the tool, that will handle building of your project.
This includes SSH tasks, parallel script running, gzipping and [many more](http://ant.apache.org/manual/tasklist.html).

## Dependencies ##

- PHP >= 5.3.2

## License ##

The code for PHPAnt is distributed under the terms of the MIT license (see [LICENSE](LICENSE)).
