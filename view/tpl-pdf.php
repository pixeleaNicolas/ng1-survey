<style>
    *    {color: #004B54; font-size:12px; word-wrap: break-word;}


H1   {color: #DC4B36; font-size:20px; margin-top: 100px}
H2   {color: #DC4B36;font-size:16px; margin-top:50px}
li{
    padding: 8px ;
}
H3{color: #00ACAF;}
P{
    padding: 0 30px 0 15px;
    margin-bottom: 5px;
    margin-top:5px;
    max-width: 90%;

}
P.selected{
    color:#00ACAF;
    font-size:13px; }

}
</style>
<?php 
echo $content= get_field('pdf_content',$post_id);