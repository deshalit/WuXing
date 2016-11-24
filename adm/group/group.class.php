<?php
require_once('dict.class.php');

abstract class GroupDBManager
{  
    public function Load(GroupManager $groupman)
    {
        if (!$this->checkConnection()) {
            return false;
        }
        try {
            $groupman->groups = $this->doLoadGroups();
            if (!$groupman->groups) {
                return false;
            }
            $groupman->members = Array();
            foreach ($groupman->groups as $group) {
                $group->id *= 1; // id must be integer
                $group->members = $this->doLoadGroupMembers($group->id);
                //var_dump($group->members);
                if ($group->members) {
                    foreach ($group->members as $member) {
                        $member->id *= 1; // id must be integer
                        $member->values = $this->doLoadValues($member->id);
                        if ($member->values) {
                           $groupman->members[$member->id] = Array(); 
                           foreach($member->values as $key=>$value) {
                               $groupman->members[$member->id][$key] = $value;   
                           }    
                        }    
                    }
                }
            }
            return true;
        } finally {
            $this->closeConnection();
        }
    }
    public function addGroup($groupName)
    {
        if ($this->checkConnection()) {
            return $this->doAddGroup($groupName);
        }  
        return false;
    }

    public function renameGroup($groupID, $newGroupName)
    {
        if ($this->checkConnection()) {
            return $this->doRenameGroup($groupID, $newGroupName);
        }
        return false;
    }

    public function deleteGroup($groupID)
    {
        if ($this->checkConnection()) {
            return $this->doDeleteGroup($groupID);
        }
        return false;
    }

    public function addMember($memberName, $groupID)
    {
        if ($this->checkConnection()) {
            return $this->doAddMember($memberName, $groupID);
        }
        return false;
    }

    public function deleteMember($memberID)
    {
        if ($this->checkConnection()) {
            return $this->doDeleteMember($memberID);
        }
        return false;
    }

    public function renameMember($memberID, $newMemberName)
    {
        if ($this->checkConnection()) {
            return $this->doRenameMember($memberID, $newMemberName);
        }
        return false;
    }
    
    public function insertValue($memberID, $elementID, $value) 
    {
        if ($this->checkConnection()) {
            return $this->doInsertValue($memberID, $elementID, $value);
        }
        return false;        
    }
    
    public function updateValue($memberID, $elementID, $value) 
    {
        if ($this->checkConnection()) {
            return $this->doUpdateValue($memberID, $elementID, $value);
        }
        return false;        
    }    
    
    public function deleteValue($memberID, $elementID) 
    {
        if ($this->checkConnection()) {
            return $this->doDeleteValue($memberID, $elementID);
        }
        return false;        
    }
    
    abstract protected function checkConnection();

    abstract protected function doDeleteGroup($groupID);

    abstract protected function doAddMember($memberName, $groupID);

    abstract protected function doDeleteMember($memberID);

    abstract protected function doLoadGroups();

    abstract protected function doLoadGroupMembers($groupID);

    abstract protected function doLoadValues($memberID);
    
    abstract protected function doAddGroup($groupName);

    abstract protected function doRenameGroup($groupID, $newGroupName);
    
    abstract protected function doRenameMember($memberID, $newMemberName);
    
    abstract protected function doUpdateValue($memberID, $elementID, $value);
    
    abstract protected function doDeleteValue($memberID, $elementID);
    
    abstract protected function doInsertValue($memberID, $elementID, $value);

    abstract protected function closeConnection();
}

class GroupMember
{
    public $id = 0;
    public $name = '';
    public $values;
}

class Group
{
    public $id = 0;
    public $name = '';
    public $note = '';
    public $members;
/*
    public function __construct() {
        $this->id = 0;
    }

    function __set($name, $value) {
        if ($name == 'id') {
            echo '<br/> yes! <br/>';
            $this->id = intval($value);
        }
}*/
}

class GroupManager
{
    public $groups;
    public $members;
    public $loaded = false;
    public $dbManager = NULL;

    public function getGroupValues($groupId)
    {
        $res = null;
        if ($group = $this->getGroupById($groupId)) {
            $elemSum = array_fill_keys( array_keys(Dictionary::$elemNames), 0);
            if ($group->members) {
                foreach($group->members as $member) {
                    if ($member->values) {
                        foreach($member->values as $key=>$value) {
                            $elemSum[$key] += $value;
                        }
                    }     
                }
                $totalSum = array_sum($elemSum);
                $res = array_map(function ($el) use ($totalSum) { 
                                       return strval(round($el / $totalSum, 4)); }, $elemSum);
            }   
        }
        return $res;
    }
    private function getMemberValue($memberID, $elementID) 
    {
        $res = false;
        if ($this->loaded and $this->members) {
            try {
                $res = $this->members[$memberID][$elementID];
            } catch (Exception $e) {
                $res = false;
            }    
        }    
        return $res;
    }  

    public function addGroup($groupName)
    {
        $res = false;
        if ($this->dbManager) {
            // 'dbManager ready';
            if ($res = $this->dbManager->addGroup($groupName)) {
                $this->Load();
                return $res;
            }    
        }  //   else { echo 'dbManager is not ready'; }
        return $res;
    }

    public function renameGroup($groupID, $groupName)
    {
        $res = false;
        if ($this->dbManager) {
            // 'dbManager ready';
            if ($res = $this->dbManager->renameGroup($groupID, $groupName)) {
                $this->Load();
                return $res;
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;
    }

    public function deleteGroup($groupID)
    {
        $res = false;
        if ($this->dbManager) {
            // 'dbManager ready';
            if ($res = $this->dbManager->deleteGroup($groupID)) {
                $this->Load();
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;
    }

    public function getGroupById($groupId)
    {
        if ($this->groups) { 
            foreach($this->groups as $group) {
               if ($group->id == $groupId) {
                   return $group;
                }
            }    
        }
        return false;
    }

    public function addMember($memberName, $groupID)
    {
        $res = false;
        if ($this->dbManager) {
            // 'dbManager ready';
            if ($res = $this->dbManager->addMember($memberName, $groupID)) {
                $this->Load();
                return $res;
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;
    }

    public function deleteMember($memberID)
    {
        $res = false;
        if ($this->dbManager) {
            // 'dbManager ready';
            if ($res = $this->dbManager->deleteMember($memberID)) {
                $this->Load();
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;
    }

    public function renameMember($memberID, $memberName)
    {
        $res = false;
        if ($this->dbManager) {
            // 'dbManager ready';
            if ($res = $this->dbManager->renameMember($memberID, $memberName)) {
                $this->Load();
                return $res;
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;
    }
    
    private function updateValue($memberID, $elementID, $value) 
    {
        $res = false;
        if ($this->dbManager) {
            if ($res = $this->dbManager->updateValue($memberID, $elementID, $value)) {
                $this->Load();
                return $res;
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;         
    }  

    private function insertValue($memberID, $elementID, $value) 
    {
        $res = false;
        if ($this->dbManager) {
            if ($res = $this->dbManager->insertValue($memberID, $elementID, $value)) {
                $this->Load();
                return $res;
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;         
    }
    private function deleteValue($memberID, $elementID) 
    {
        $res = false;
        if ($this->dbManager) {
            if ($res = $this->dbManager->deleteValue($memberID, $elementID)) {
                $this->Load();
                return $res;
            }
        }  //   else { echo 'dbManager is not ready'; }
        return $res;         
    }
    public function setMemberValue($memberID, $elementID, $newValue) 
    {
        $res = false;
        $oldValue = $this->getMemberValue($memberID, $elementID);
        if ( ($oldValue !== null) ) {
            if ($newValue) {
                if ($newValue != $oldValue) {
                    $res = $this->updateValue($memberID, $elementID, $newValue);
                }    
            } else {
                $res = $this->deleteValue($memberID, $elementID);
            }    
        } else {
            if ($newValue) { 
                $res = $this->insertValue($memberID, $elementID, $newValue);
            }
        }
        return $res;
    }    
    
    public function Load()
    {   
        if ($this->dbManager) {
            $this->loaded = $this->dbManager->Load($this);
            return $this->loaded;
        }
        return false;
    }
}

$GroupManager = NULL;