OpenFLUID-WaresHub
==================

OpenFLUID-WaresHub is a system for management and sharing of OpenFLUID wares (simulators, observers, builder-extensions) and more.
It is mainly based on git, apache and php scripts

OpenFLUID-WaresHub uses definitions for wares to instanciate corresponding git repositories, apache based access control, and web reporting


## Management tool

### Usage

ofwareshub <defsrepos-name> <command> [arg1] [arg2] [...]


### Examples of use


#### Display global config

    ofwareshub global displayconfig 


#### Check global config

    ofwareshub global checkconfig


#### Check definitions config

    ofwareshub <defsrepos-name> checkconfig


#### Create a ware definition in the definitions repository

    ofwareshub <defsrepos-name> createdef simulator my.simulator 


#### Create an instance repository

    ofwareshub <defsrepos-name> initinstance


#### Update an instance repository

    ofwareshub <defsrepos-name> updateinstance apachemain


#### Create a simulator in the git based hosting structure

    ofwareshub <defsrepos-name> createware simulator my.simulator
    
    
#### Update settings of a simulator a simulator in the git based hosting structure
    
    ofwareshub <defsrepos-name> updateware simulator my.simulator users
 
    ofwareshub <defsrepos-name> updateware simulator my.simulator mailinglist
     
    ofwareshub <defsrepos-name> updateware simulator my.simulator description 
    
    ofwareshub <defsrepos-name> updateware simulator my.simulator allsettings 
    
    