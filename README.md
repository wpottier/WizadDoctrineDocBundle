WizadDoctrineDocBundle
======================

The **WizadDoctrineDocBundle** bundle allows you to generate a decent documentation for your doctrine model schema.

## Installation ##

Add this bundle to your `composer.json` file:

    {
        "require": {
            "wizad/doctrine-doc-bundle": "dev-master"
        }
    }

Register the bundle in `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Wizad\DoctrineDocBundle\WizadDoctrineDocBundle(),
        );
    }

## Usage ##

    php app/console doctrine:generate:documentation <output_path>
