<!DOCTYPE html>
<html lang="zh_TW">

<head>
  <meta charset="UTF-8">
  <meta name="author" content="Max and his little friend">
  <meta name="description" content="CRUD in PDO">
  <meta name="color-scheme" content="light|dark">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <script src="javascript.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto+Sans+TC&display=swap">
  <link rel="stylesheet" type="text/css" href="style.css">
  <title>PDO_CRUD_R</title>
</head>

<body>
  <?require_once "connDB.php";?>
  <?$connDB = new ConnDB;?>
  <?$Read = new Read($connDB);?>
  <h1>
    <?echo $Read->title(); ?>管理
  </h1>
  <div id="main">

    <form id="DBform" action="http://max.com:666/Maxfirstone/fullStack/PDOCRUD/index.php" method="GET" align="center">
      資料表選擇：<select name="DBSelect">
        <?echo $Read->DBSelect(); ?>
      </select>
      <br><br>
      <input type="submit" value="確認" class="commit">
    </form>
    <?echo $Read->DBcount(); ?>
    <p align="center">
      <?echo $Read->success(); ?>
      <?echo $Read->CreateLink() ?>
      &emsp;
      <?echo $Read->DeleteLink() ?>
    </p>

    <form id="formR" action="http://max.com:666/Maxfirstone/fullStack/PDOCRUD/index.php" method="POST">
      <table class="DBTable">
        <thead>
          <?echo $Read->fromTitle($connDB); ?>
        </thead>
        <tbody>
          <tr>
            <?echo $Read->fromContent(); ?>
          </tr>
        </tbody>
      </table>
    </form>
    <p>
      <?echo $Read->pageUrl(); ?>
    </p>
  </div>

</body>

</html>

<?php

use grammar\Inflect;

class Read
{
    protected static $connDB;
    protected $tblNames;

    public function __construct(connDB $connDB)
    {
        require 'Inflect.php';
        self::$connDB = $connDB->ConnDB();
        $this->tblNames = $connDB->tblName();
        // $connDB = null;
    }
    public function title()
    {
        if (!isset($_GET['DBSelect'])) {
            return "專案資料";
        }
        $title = mb_convert_case($_GET['DBSelect'], MB_CASE_TITLE, "UTF-8");
        $title = $this->Singular($title);
        return $title;
    }
    public function DBcount()
    {
        if (!isset($_GET['DBSelect'])) {
            return null;
        }
        $pageData = $this->page();
        $count = $pageData["count"];
        $total_page = $pageData["total_page"];
        $page = $pageData["page"];
        $htmlTag = "<p align='center' >目前有{$count}筆資料。<br>目前在第{$page}頁，總共有{$total_page}頁。</p>";
        return $htmlTag;
    }
    public function success()
    {
        if (isset($_GET['deleteOne'])) {
            return "刪除該資料成功(`･∀･)b";
        }
        if (isset($_GET['deleteMultiple'])) {
            return "刪除這些資料成功(`･∀･)b";
        }
        return null;
    }
    public function CreateLink()
    {
        if (!isset($_GET['DBSelect'])) {
            return null;
        }
        $title = $this->title();
        $title = $this->Singular($title);
        $htmlTag = "<button><a href='create.php?DBSelect={$_GET['DBSelect']}' >";
        $htmlTag .= "新增一筆" . $title;
        $htmlTag .= "</a></button>";
        return $htmlTag;
    }
    public function DeleteLink()
    {
        if (!isset($_GET['DBSelect'])) {
            return null;
        }
        $htmlTag = "<button><a href=\"#\" onclick='delAll();'>刪除勾選資料</a></button>&emsp;";
        return $htmlTag;
    }
    public function DBSelect()
    {
        $tblNames = $this->tblNames;
        $htmlTag = "";
        foreach ($tblNames as $key => $value) {
            $htmlTag .= "<option value=" . $value . ">" . $value . "</option>";
        }
        return $htmlTag;
    }
    protected function Singular($String)
    {
        $SingularString = Inflect::singularize($String);
        return $SingularString;
    }
    public function fromTitle(connDB $connDB)
    {
        if (!isset($_GET['DBSelect'])) {
            exit;
        }
        $this->fieldMeta = $connDB->fieldMeta();
        $result = $this->fieldMeta;
        $htmlTag = "";
        foreach ($result as $key => $value) {
            $htmlTag .= "<th>$key</th>";
        }
        $htmlTag .= "<td>哈哈打錯字</td>";
        $htmlTag .= "<td>刪除單項資料</td>";
        $htmlTag .= "<td>勾選資料</td>";
        $result = null;
        $conn = null;
        return $htmlTag;
    }
    public function fromContent()
    {
        if (!isset($_GET['DBSelect'])) {
            exit;
        }
        $pageData = $this->page();
        $start = $pageData["start"];
        $per_page = $pageData["per_page"];
        $conn = self::$connDB;
        $sql = "SELECT * FROM `" . $_GET['DBSelect'] . "`limit " . $start . "," . $per_page;
        $result = $conn->prepare($sql);
        $result->execute();
        $htmlTag = "";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $htmlTag .= "<tr>";
            foreach ($row as $key => $value) {
                if ($key == "enabled") {
                    if ($value == 1) {
                        $value = "true";
                    } else {
                        $value = "false";
                    }
                }
                $htmlTag .= "<td>" . $value . "</td>";
            }
            if (isset($row["id"])) {
                $htmlTag .= "<td class=face><a href='update.php?id={$row["id"]}'><button>(ﾟ∀。)</button></a></td>";
                $htmlTag .= "<td class=face><a href='delete.php?DBSelect={$_GET['DBSelect']}&id={$row["id"]}'><button>(×ω×)</button></a></td>";
                $htmlTag .= "<td><input type='checkbox' name='del[]' value='{$row['id']}'></td>";
                $htmlTag .= "</tr>";
            } else {
                $id1k = key($row);
                $id1v = array_shift($row);
                $id2k = key($row);
                $id2v = array_shift($row);
                $htmlTag .= "<td class=face><a href='update.php?DBSelect={$_GET['DBSelect']}&{$id1k}={$id1v}&{$id2k}={$id2v}'><button>(ﾟ∀。)</button></a></td>";
                $htmlTag .= "<td class=face><a href='delete.php?DBSelect={$_GET['DBSelect']}&{$id1k}={$id1v}&{$id2k}={$id2v}'><button>( ×ω× )</button></a></td>";
                $htmlTag .= "<td><label>
                <input type='checkbox' name='del_{$id1k}[]' value='{$id1v}'>
                <span class='checkbox'>( ﾟдﾟ )</span>
                <input type='hidden' name='del_{$id2k}[]' value='{$id2v}'></label></td>";
                $htmlTag .= "</tr>";
            }
        }
        $result = null;
        $conn = null;
        return $htmlTag;
    }
    protected function page()
    {
        if (!isset($_GET['DBSelect'])) {
            exit;
        }
        try {
            $conn = self::$connDB;
            $sql = "SELECT * FROM `" . $_GET['DBSelect'] . "`";
            $result = $conn->prepare($sql);
            $result->execute();
        } catch (PDOException $e) {
            die("<span class=errorMessage>" . "ヽ(´;ω;`)ﾉ!: " . $e->getMessage() . "</span><br/>");
        }
        $count = $result->rowCount();
        $nowPage = 1;
        if (isset($_GET['Page'])) {
            $nowPage = $_GET['Page'];
        }
        $per_page = 12;
        $total_page = ceil($count / $per_page);
        $prev = $nowPage - 1;
        $next = $nowPage + 1;
        $start = ($nowPage - 1) * $per_page;
        $pageData = ["per_page" => $per_page, "start" => $start, "total_page" => $total_page, "count" => $count, "page" => $nowPage, "prev" => $prev, "next" => $next];
        $result = null;
        $conn = null;
        return $pageData;
    }
    public function pageUrl()
    {
        $pageData = $this->page();
        $total_page = $pageData["total_page"];
        $prev = $pageData["prev"];
        $next = $pageData["next"];
        $page = $pageData["page"];
        $htmlTag = "";
        if ($total_page == 1) {
            return "<button><a href='?DBSelect={$_GET['DBSelect']}'>沒有更多資料了(´・ω・`)</a></button>";
        }
        switch ($page) {
            case 1:
                $htmlTag = "<button><a href='?DBSelect={$_GET['DBSelect']}&Page=$next'>下一頁</a></button>
                <button><a href='?DBSelect={$_GET['DBSelect']}&Page=$total_page'>尾頁</a></button>";
                break;
            case $total_page;
                $htmlTag = "<button><a href='?DBSelect={$_GET['DBSelect']}&Page=1'>首頁</a></button>
                <button><a href='?DBSelect={$_GET['DBSelect']}&Page=$prev'>上一頁</a></button>";
                break;
            default:
                $htmlTag = "<button><a href='?DBSelect={$_GET['DBSelect']}&Page=1'>首頁</a></button>
                <button><a href='?DBSelect={$_GET['DBSelect']}&Page=$prev'>上一頁</a></button>
                <button><a href='?DBSelect={$_GET['DBSelect']}&Page=$next'>下一頁</a></button>
                <button><a href='?DBSelect={$_GET['DBSelect']}&Page=$total_page'>尾頁</a></button>";
                break;
        }
        return $htmlTag;
    }
}