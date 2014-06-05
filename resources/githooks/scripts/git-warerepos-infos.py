#!/usr/bin/env python

import os
import subprocess
import re


###################################################


def getBranchesList():

  Output = subprocess.check_output(["git","for-each-ref","--format=%(refname:short)","refs/heads"])
  Branches = Output.splitlines()
  return Branches


###################################################


def generateCommitsHistoryFiles(BranchName):

  subprocess.call(["mkdir","-p","wareshub-data/"+BranchName])

  Output = subprocess.check_output(["git","log","--format=%H:::%an:::%ae:::%ad:::%s",BranchName])
  CommitsLines = Output.splitlines()

  CommitsFile = open("wareshub-data/"+BranchName+"/commits-history.json","w")
  FirstLine = True

  CommitsFile.write("{")

  for Line in CommitsLines:
    Infos = Line.split(":::")
    if len(Infos) == 5 :
      if not FirstLine :
        CommitsFile.write(",")
      CommitsFile.write("\n")
      CommitsFile.write("  \"%s\" : {\n" % Infos[0])
      CommitsFile.write("    \"authorname\" : \"%s\",\n" % Infos[1])
      CommitsFile.write("    \"authoremail\" : \"%s\",\n" % Infos[2])
      CommitsFile.write("    \"date\" : \"%s\",\n" % Infos[3])   
      CommitsFile.write("    \"subject\" : \"%s\"\n" % Infos[4].replace("\"","\\\""))
      CommitsFile.write("  }")
      FirstLine = False

  CommitsFile.write("\n}\n")
  CommitsFile.close()


###################################################


def extractBranchFile(BranchName,FileName):

  Output = ""
  try :
    FNULL = open(os.devnull, 'w')
    Output = subprocess.check_output(["git","show",BranchName+":"+FileName],stderr=FNULL)
    ExtractedFile = open("wareshub-data/"+BranchName+"/"+FileName,"w")
    ExtractedFile.write(Output)
    ExtractedFile.close()
    FNULL.close()
  except :
    pass  
  

###################################################


def generateGitStatsFile(BranchesList):
    
  GitStatsFile = open("wareshub-data/gitstats.json","w")
  
  # branches
  
  GitStatsFile.write("{\n  \"branches\" : [")
  
  FirstLine = True
  
  for Branch in Branches:
    if not FirstLine :
      GitStatsFile.write(",")
    GitStatsFile.write("\n")
    GitStatsFile.write("    \"%s\"" % Branch)
    FirstLine = False

  GitStatsFile.write("\n  ],\n")

  
  # commiters stats
  
  Output = subprocess.check_output(["git","shortlog","-s","-n","-e","-w0","--all"])
  CommitersLines = Output.splitlines()

  GitStatsFile.write("  \"committers\" : {")

  FirstLine = True

  for Line in CommitersLines :
    Commiter = Line.split("\t")
    
    if len(Commiter) == 2 :
      Count = Commiter[0].strip()
      
      NameEmail = Commiter[1].strip().split("<")
      Name = NameEmail[0].strip()
      Email = NameEmail[1].replace('>','')
      
      if not FirstLine :
        GitStatsFile.write(",")
      GitStatsFile.write("\n")
      
      GitStatsFile.write("    \"%s\" : {\n" % Name)
      GitStatsFile.write("      \"email\" : \"%s\",\n" % Email)
      GitStatsFile.write("      \"count\" : \"%s\"\n" % Count)
      GitStatsFile.write("    }")
      
      FirstLine = False
     
  GitStatsFile.write("\n  }\n}\n")  


###################################################


def setDefaultBranch(BranchesList):
  
  ValidBranches = []
  
  for Branch in BranchesList:
    ValidFound = re.match("openfluid-(\d+\.\d+(\.\d+)*)$",Branch)
    if ValidFound:
      ValidBranches.append(Branch)
  
  DefaultBranch = max(ValidBranches)

  subprocess.call(["git","symbolic-ref","-q","HEAD","refs/heads/"+DefaultBranch])



###################################################
# main
###################################################

subprocess.call(["rm","-rf","wareshub-data"])
subprocess.call(["mkdir","-p","wareshub-data"])

Branches = getBranchesList()

for Branch in Branches:
  generateCommitsHistoryFiles(Branch)
  extractBranchFile(Branch,"wareshub.json")
  extractBranchFile(Branch,"README")
  extractBranchFile(Branch,"README.md")
  extractBranchFile(Branch,"LICENSE")
  extractBranchFile(Branch,"COPYING")

generateGitStatsFile(Branches)

setDefaultBranch(Branches)

