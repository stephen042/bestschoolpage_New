<style>
    /* ===============================
   Footer
================================== */

.footer{
    width:100%;
    clear:both;
    display:block;
    background:#fff;
    border-top:1px solid #e5e5e5;
    padding:15px 20px;
    margin-top:30px;
    position:relative;
    left:0;
    right:0;
    bottom:0;
    z-index:10;
}

.footer p{
    margin:0;
    color:#666;
    font-size:13px;
    line-height:22px;
}

@media (max-width:768px){

    .footer{
        width:100%;
        margin-left:0 !important;
        left:0 !important;
        right:0 !important;
        padding:15px;
        text-align:center;
    }

    .footer .container,
    .footer .container-fluid{
        width:100%;
        padding-left:15px;
        padding-right:15px;
    }
}
</style>
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 text-center">
                <p>
                    &copy; <?php echo date('Y'); ?>
                    <?php echo isset($schoolName) ? htmlspecialchars($schoolName) : 'School Management System'; ?>.
                    All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>
