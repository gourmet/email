<?=
foreach (explode("\n", $content) as $para) {
    echo $this->Email->para(null, $para);
}
