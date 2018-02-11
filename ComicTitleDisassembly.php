<?php

class ComicTitleDisassembly{

    // インスタンス
    private static $instance;

    // タイトル
    private $title = null;
    private $title_custom = null;

    // パターン : 巻数と思われる文字列
    private $pattern_of_numeric_characters = '
        \d+(?:\.\d+)?
        |[ivx]+
        |[IVX]+
        |[０-９]+(?:．[０-９]+)?
        |[〇一二三四五六七八九十百千万零壱弍参肆伍陸漆捌玖壹貳參拾佰仟萬]+
        |①
        |⑩
        |②
        |③
        |④
        |⑤
        |⑥
        |⑦
        |⑧
        |⑨
        |上
        |下
        |中
        |前
        |後
    ';

    // 巻数に使われる接頭語
    private $pattern_of_prefix_of_book_position = '/
        \#\s*
        |episode\.?\s*
        |lv\.?\s*
        |level\.?\s*
        |vol(?:ume)?\.?\s*
        |その\s*
        |巻(?:の|ノ|之)?\s*
        |第\s*
    '; 

    // 巻数に使われる接尾語
    private $pattern_of_suffix_of_book_position = '
        \s*話
        |\s*巻
        |\s*版
    ';

    private $pattern_of_volume_prefix = '
        episode\.?\s*
        |\#
        |Episode\.?\s*
        |EPISODE\.?\s*
        |vol(?:ume)?\.?\s*
        |Vol(?:ume)?\.?\s*
        |VOL(?:UME)?\.?\s*
        |その\s*
        |第\s*
    ';

    // コンストラクタ
    function __construct()
    {
    }

    // インスタンス化(シングルトン)
    public static function getInstance()
    {
       if (!self::$instance) self::$instance = new Singleton;
       return self::$instance;
    }

    // タイトルの設定
    public function set_title(String $title): void
    {
        $this->title = $title;
        $this->title_custom = (function (string $title) {
            $title = htmlentities($title);
            $title = mb_convert_kana($title, 'asKV', 'UTF-8');
            $title = preg_replace("/　+/", " ", $title); // `"ウメハラ  FIGHTING GAMERS!"` -> `"ウメハラ FIGHTING GAMERS!"`
            $title = preg_replace("/\s+/", " ", $title); // `"ウメハラ  FIGHTING GAMERS!"` -> `"ウメハラ FIGHTING GAMERS!"`
            $title = str_replace("‐ ", "-", $title); // `"D.Gray‐ man"` -> `"D.Gray-man"`
            $title = preg_replace("/\w+ー\w+/", "-", $title); // `"D.Grayーman"` -> `"D.Gray-man"`
            $title = preg_replace("/!\s+!/", "!!", $title); // `"ばくおん! !"` -> `"ばくおん!!"`
            $title = preg_replace("/!\s+」/", "!」", $title); // `"まおゆう魔王勇者 「この我のものとなれ、勇者よ」「断る! 」"` ->  `"まおゆう魔王勇者 「この我のものとなれ、勇者よ」「断る!」"`
            // `"ヒナまつり 11 (ヒナまつり)  (ビームコミックス(ハルタ) )"` -> `"ヒナまつり 11 (ヒナまつり)  (ビームコミックスハルタ )"`
            // `"魔法使いの嫁 通常版 4 (BLADE COMICS)"` -> `"魔法使いの嫁 通常版 4"`
            // `"アド・アストラ 1 ─ スキピオとハンニバル─ "` -> `"アド・アストラ 1"`
            $title = preg_replace("/\s+通常版|(未分類)/","",$title); // `"魔法使いの嫁 通常版"` -> `"魔法使いの嫁"`
            return $title;
        })($title);
    }

    // タイトルの取得
    public function get_series(): String
    {
        return $this->title_custom;
    }

    // 巻数の取得
    public function get_position(): String
    {
        preg_match("/\A0+([1-9]+)/", $this->title_custom, $matches);
        if($matches){
            return $matches[1];
        } else {
            return 0;
        }
    }

    final function __clone()
    {
        throw new \Exception('Clone is not allowed against' . get_class($this)); 
    }

}

$obj = new ComictitleAnalysis();
$obj->set_title($argv[1]);
echo $obj->get_series() . "\n";
echo $obj->get_position() . "\n";

