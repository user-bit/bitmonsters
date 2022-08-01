<?php class AjaxAdminController extends BaseController
{
    function __construct($registry, $params)
    {
        $this->registry = $registry;
        parent::__construct($registry, $params);
    }

    function activeAction()
    {
        if (isset($_POST['id'], $_POST['tb'])){
            if($_POST['liqpay']==1){
                echo json_encode($this->model->active($_POST['id'], $_POST['tb'], $_POST['tb2'],1));
            }else{
                echo json_encode($this->model->active($_POST['id'], $_POST['tb'], $_POST['tb2']));
            }
        }
    }

    function sortAction()
    {
        if (isset($_POST['arr'], $_POST['tb'])) echo json_encode($this->model->sortTable($_POST['arr'], $_POST['tb'], $_POST['tb2']));
    }
}