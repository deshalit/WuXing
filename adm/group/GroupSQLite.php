<?php
require_once('group.class.php');

const DB_PATH = 'DB/wuxing.sqlite';

const QUERY_GROUPLIST    = 'SELECT id, name, note FROM groups WHERE id > 0';
const QUERY_GROUPMEMBERS = 'SELECT id, name FROM members WHERE id_group = :ID ORDER BY name';
const QUERY_VALUES       = 'SELECT id_element, value FROM member_data WHERE id_member = :ID ORDER BY id_element';
const QUERY_ADDGROUP     = 'INSERT INTO groups (name) VALUES (:NAME)';
const QUERY_DELETEGROUP  = 'DELETE FROM groups WHERE id = :ID';
const QUERY_RENAMEGROUP  = 'UPDATE groups SET name = :NAME WHERE id = :ID';
const QUERY_DELETEMEMBER = 'DELETE FROM members WHERE id = :ID';
const QUERY_ADDMEMBER    = 'INSERT INTO members (name, id_group) VALUES (:NAME, :GROUPID)';
const QUERY_RENAMEMEMBER = 'UPDATE members SET name = :NAME WHERE id = :ID';
const QUERY_UPDATEVALUE  = 'UPDATE member_data SET value = :VAL WHERE id_member = :MID AND id_element = :EID';
const QUERY_INSERTVALUE  = 'INSERT INTO member_data (id_member, id_element, value) VALUES (:MID, :EID, :VAL)';
const QUERY_DELETEVALUE  = 'DELETE FROM member_data WHERE id_member = :MID AND id_element = :EID';

class GroupSQLite extends GroupDBManager
{
    private $conn = NULL;
    private static $numToId = Array ('1' => 'F', '2' => 'E', '3' => 'M', '4' => 'W', '5' => 'T');
    private static $idToNum = Array ('F' => 1, 'E' => 2, 'M' => 3, 'W' => 4, 'T' => 5);

    protected function checkConnection()
    {
        $res = true;
        if (!$this->conn) {
            $res = true;
            try {
                $this->conn = new PDO('sqlite:' . DB_PATH);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    protected function doDeleteGroup($groupID)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_DELETEGROUP);
            $stmt->bindParam(":ID", $groupID, PDO::PARAM_INT);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $exception) {
            $res = 0;
        }
        return $res;
    }

    protected function doLoadGroups()
    {
        try {
            $stmt = $this->conn->prepare(QUERY_GROUPLIST);

            $stmt->execute();
            $res = $stmt->fetchALL(PDO::FETCH_CLASS, 'Group');
        } catch (PDOException $e) {
            $res = NULL;
        }
        return $res;
    }

    protected function doLoadGroupMembers($groupID)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_GROUPMEMBERS);
            $stmt->bindParam(":ID", $groupID, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetchALL(PDO::FETCH_CLASS, 'GroupMember');
        } catch (PDOException $e) {
            $res = NULL;
        }
        return $res;
    }

    protected function doLoadValues($memberID)
    {   $res = NULL;
        try {
            $stmt = $this->conn->prepare(QUERY_VALUES);
            $stmt->bindParam(":ID", $memberID, PDO::PARAM_INT);
            $stmt->execute();
            $allValues = $stmt->fetchALL(PDO::FETCH_ASSOC);
            if (count($allValues) > 0) {
                $res = Array();
                foreach ($allValues as $record) {
                    $elKey = self::$numToId[$record['id_element']];
                    $elValue = $record['value'];
                    $res[$elKey] = $elValue * 1;
                }
            }
        } catch (PDOException $e) {
            $res = NULL;
        }
        return $res;
    }

    protected function doAddGroup($groupName)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_ADDGROUP);
            $stmt->bindParam(":NAME", $groupName, PDO::PARAM_STR);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $this->conn->lastInsertId();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; //$e->getMessage(); //echo $res;
        }
        return $res;
    }

    protected function doAddMember($memberName, $groupID)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_ADDMEMBER);
            $stmt->bindParam(":NAME", $memberName, PDO::PARAM_STR);
            $stmt->bindParam(":GROUPID", $groupID, PDO::PARAM_INT);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $this->conn->lastInsertId();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; //$e->getMessage(); //echo $res;
        }
        return $res;
    }

    protected function doDeleteMember($memberID)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_DELETEMEMBER);
            $stmt->bindParam(":ID", $memberID, PDO::PARAM_INT);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $exception) {
            $res = 0;
        }
        return $res;
    }

    protected function doRenameMember($memberID, $newMemberName)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_RENAMEMEMBER);
            $stmt->bindParam(":ID", $memberID, PDO::PARAM_INT);
            $stmt->bindParam(":NAME", $newMemberName, PDO::PARAM_STR);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; //$e->getMessage(); //echo $res;
        }
        return $res;
    }
    
    protected function doRenameGroup($groupID, $newGroupName)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_RENAMEGROUP);
            $stmt->bindParam(":ID", $groupID, PDO::PARAM_INT);
            $stmt->bindParam(":NAME", $newGroupName, PDO::PARAM_STR);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; //$e->getMessage(); //echo $res;
        }
        return $res;
    }
    
    protected function doDeleteValue($memberID, $elementID)
    {
        try {
            $stmt = $this->conn->prepare(QUERY_DELETEVALUE);
            $stmt->bindParam(":MID", $memberID, PDO::PARAM_INT);
            $stmt->bindParam(":EID", self::$idToNum[$elementID], PDO::PARAM_INT);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $exception) {
            $res = 0;
        }
        return $res;
    }
    protected function doInsertValue($memberID, $elementID, $value) 
    {
        try {
            $stmt = $this->conn->prepare(QUERY_INSERTVALUE);
            $stmt->bindParam(":MID", $memberID, PDO::PARAM_INT);
            $stmt->bindParam(":EID", self::$idToNum[$elementID], PDO::PARAM_INT);
            $stmt->bindParam(":VAL", $value, PDO::PARAM_INT);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; //$e->getMessage(); //echo $res;
        }
        return $res;
    }
    protected function doUpdateValue($memberID, $elementID, $value) 
    {   echo 'doUpdateValue' . "\n";
        try {
            $stmt = $this->conn->prepare(QUERY_UPDATEVALUE);
             $stmt->bindParam(":MID", $memberID, PDO::PARAM_INT);
            $stmt->bindParam(":EID", self::$idToNum[$elementID], PDO::PARAM_INT);
            $stmt->bindParam(":VAL", $value, PDO::PARAM_INT);
            $this->conn->beginTransaction();
            $stmt->execute();
            $res = $stmt->rowCount();
            $this->conn->commit();
        } catch (PDOException $e) {
            //$res = NULL;
            $res = 0; echo $e->getMessage(); //echo $res;
        }
        return $res;
    }
    protected function closeConnection()
    {
        if (!$this->conn) {
            $this->conn = NULL;
        }
    }
}