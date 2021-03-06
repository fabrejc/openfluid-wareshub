Administration tool
===================

## Usage

ofwareshub-admin [system] < command > [arg1] [arg2] [...]

The ofwareshub-admin must be run either from the root directory 
of the definitions sets you want to manage, 
or with the OFWHUB_DEFSSET_PATH environment variable set
to the root directory of the definitions sets you want to manage.


## Examples of use


### Show system config

    ofwareshub-admin system showconfig 


### Check system config

    ofwareshub-admin system checkconfig


### Show definitions set config

    ofwareshub-admin showconfig


### Check definitions set config

    ofwareshub-admin checkconfig


### Create a ware definition in the definitions set

    ofwareshub-admin createdef simulator my.simulator 


### Create an instance repository

    ofwareshub-admin initinstance


### Update an instance repository

    ofwareshub-admin updateinstance


### Show report about the definitions set

    ofwareshub-admin showreport


### Create a simulator in the git based hosting structure

    ofwareshub-admin createware simulator my.simulator
    
    
### Update settings of a simulator a simulator in the git based hosting structure
    
    ofwareshub-admin updateware simulator my.simulator users
 
    ofwareshub-admin updateware simulator my.simulator mailinglist
     
    ofwareshub-admin updateware simulator my.simulator description 
    
    ofwareshub-admin updateware simulator my.simulator allsettings 
    
    