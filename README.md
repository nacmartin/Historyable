# Doctrine 2 historyable extension

This package contains the Historyable extension, that helps managing entities that describe (but not contain) changes of another entity. (Currently only **Annotation** mapping and ORM suport.

This code is based upon the package https://github.com/l3pp4rd/DoctrineExtensions

## What is this for

The purpose of this extension is to have an entity with an autoincremented field that depends on the value of some other field. Better to explain it with an example:

We have Todo list app. Each TODO list can have many users. If two users are editing the same TODO, it would be nice to sync their changes somehow. With this extension we can have a entity "History", that keeps a log (some kind of description) of the changes performed in that Todo, and a field Status, that represents the version of the TODO.

If we create a new History for the resource 3 (TODO list with id 3), the extension will look into the database for the Histories of resource 3, with the bigger Status number (4, for instance) and it will increment it, so that the new History has an Status of 5.

We can add more fields to the History entity, for instance some description useful to describe the change. If we look at some point for Histories for resource == 3, we can have something like:

 * status:0, action: "TODO created".
 * status:1, action: "new TODO item with id 67".
 * status:2, action: "new TODO item with id 69".
 * status:3, action: "item 67 moved after item 69".
 * status:4, action: "item with id 67 marked as done".
 * status:5 ...

    `/**
    * @ORM\Table(name="history")
    * @ORM\Entity()
    * @Nacmartin\Historyable
    */

    class History
    {
    
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;
    
        /**
         * @Nacmartin\RefVersion
         * @ORM\ManyToOne(targetEntity="Todo", inversedBy="histories")
         * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
         */
        private $resource;
    
        /**
         * @Nacmartin\Status
         * @ORM\Column(type="integer")
         */
        private $status;
    
        /**
         * @ORM\Column(type="array")
         */
        private $action;`

This way we can know what is the last version number of a resource, let the clients know it and if they are out of sync, so that they can take care and ask for a refresh, or whatever makes sense in your application.

This is my personal use case. But in a general sense, what this extension does is to keep an autoincrementable field (Status) based on the value of another field (RefVersion).
