<?php
//TO DO: need to remove this
if(isset($page_title)){
    $pageTitle = $page_title;
}
$userHighestPermOnPage = -1;
?>



<div class="sidebar" data-image="<?php echo $rootUrl; ?>assets/img/sidebar/sideBarBg.jpg">
    <div class="sidebar-wrapper">

        <div class="logo">
     
            <a href="<?php echo $rootUrl; ?>views/dashboard/" class="simple-text">
                <img src="<?php echo $rootUrl; ?>assets/img/nirvana_logo.jpg">
            </a>
            
        </div>
        <ul class="nav">
    
        <?php
        if (array_key_exists("Dashboard", $modulesWithAccess)) {
            $selectedTab = "";
            if ($pageTitle=='Dashboard') {
                $selectedTab = "selected-tab";
                $userHighestPermOnPage = $modulesWithAccess["Dashboard"];
            }
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading <?php echo $selectedTab; ?>" >
                        <h4 class="panel-title">
                        <span class="fa fa-dashboard" style="font-size: 24px;    width: 30px;    margin-right: 9px;"></span>
                            <a href="<?php echo $rootUrl; ?>views/dashboard/">Dashboard</a>
                        </h4>
                    </div>
                </div>
            </li>
        <?php
        }
        
        if (array_key_exists("ClientDashboard", $modulesWithAccess)) {
            $selectedTab = "";
            if ($pageTitle=='Client Dashboard') {
                $selectedTab = "selected-tab";
                $userHighestPermOnPage = $modulesWithAccess["ClientDashboard"];
            }
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading <?php echo $selectedTab; ?>">
                        <h4 class="panel-title">
                            <span class="fa fa-dashboard"></span>
                            <a href="<?php echo $rootUrl; ?>views/dashboard/client/">Client owner dashboard</a>
                        </h4>
                    </div>
                </div>
            </li>
        <?php
        }
        
        if (array_key_exists("PerformanceSnapshot", $modulesWithAccess)) {
            $selectedTab = "";
            if ($pageTitle=='Performance Snapshot') {
                $selectedTab = "selected-tab";
                $userHighestPermOnPage = $modulesWithAccess["PerformanceSnapshot"];
            }
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading <?php echo $selectedTab; ?>">
                        <h4 class="panel-title">
                            <i>
                                <img width="30px" height="25px" src="<?php echo $rootUrl; ?>assets/img/sidebar/performance.png">
                            </i>
                            <a href="<?php echo $rootUrl; ?>views/performance-snapshot.php">Performance Snapshot</a>
                        </h4>
                    </div>
                </div>
            </li>
        <?php
        }

        if (array_key_exists("NAVReport", $modulesWithAccess)) {
            $selectedTab = "";
            if ($pageTitle=='NAV Report') {
                $selectedTab = "selected-tab";
                $userHighestPermOnPage = $modulesWithAccess["NAVReport"];
            }
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading <?php echo $selectedTab; ?>" >
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/reports.png">
                            </i>
                            <a href="<?php echo $rootUrl; ?>views/nav-report.php">NAV Report</a>
                        </h4>
                    </div>
                </div>
            </li>
        <?php
        }
        
        if (array_key_exists("ManageBrokers", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageBrokers.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#manageBrokerSubItems">
                                Manage Brokers<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="manageBrokerSubItems" class="panel-collapse collapse 
                    <?php echo in_array($pageTitle, array('Manage Brokers','Manage Fund Accounting Transaction'))?'in':''; ?>">
                        <ul class="list-group">
                            <?php
                                if (array_key_exists("ManageBrokers", $subModulesWithAccess)) {
                                    $selectedTab = "";
                                    if ($pageTitle=='Manage Brokers') {
                                        $selectedTab = "selected-tab";
                                        $userHighestPermOnPage = $subModulesWithAccess["ManageBrokers"];
                                    }
                                ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/brokers/">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageBrokers.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Manage Brokers')?'para-new':''; ?>">
                                            Manage Brokers
                                        </p>
                                    </a>
                                </li>
                                <?php 
                                } 
                                
                                if (array_key_exists("ManageFATransactions", $subModulesWithAccess)) {
                                    $selectedTab = "";
                                    if ($pageTitle=='Manage Fund Accounting Transaction') {
                                        $selectedTab = "selected-tab";
                                        $userHighestPermOnPage = $subModulesWithAccess["ManageFATransactions"];
                                    }
                                ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/manage-fa-transactions.php">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageFATransactions.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Manage Fund Accounting Transaction')?'para-new':''; ?>">
                                            Manage FA Transaction
                                        </p>
                                    </a>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        <?php
        }
 
        if (array_key_exists("ManageClients", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClients.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#manageClientSubItems">
                                Manage Clients<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="manageClientSubItems" class="panel-collapse collapse 
                     <?php echo in_array($pageTitle, array('Manage Clients','Mapping with Amazon','Co-Mapping','Manage Slab'))?'in':''; ?>"> 
                        <ul class="list-group">
                            <?php
                            if (array_key_exists("ManageClients", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Manage Clients') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["ManageClients"];
                                }
                            ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/clients/">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClients.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Manage Clients')?'para-new':''; ?>">
                                            Manage Clients
                                        </p>
                                    </a>
                                </li>
                                
                                <?php
                                $selectedTab2 = "";
                                if ($pageTitle=='Co-Mapping') {
                                    $selectedTab2 = "selected-tab";
                                    $userHighestPermOnPage = "2";//$subModulesWithAccess["ManageClients"];
                                }
                                ?>
                                <li class="navlink2 <?php echo $selectedTab2; ?> ">
                                    <a href="<?php echo $rootUrl; ?>views/clients/clients_mapping_youtube.php">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClients.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Co-Mapping')?'para-new':''; ?>">
                                        Co-Mapping
                                        </p>
                                    </a>
                                </li>
                                <?php
                                $selectedTab2 = "";
                                if ($pageTitle=='Manage Slab') {
                                    $selectedTab2 = "selected-tab";
                                    $userHighestPermOnPage = "2";//$subModulesWithAccess["ManageClients"];
                                }
                                ?>
                                <li class="navlink2 <?php echo $selectedTab2; ?> ">
                                    <a href="<?php echo $rootUrl; ?>views/clients/clients_slab.php">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClients.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Manage Slab')?'para-new':''; ?>">
                                        Manage Slab
                                        </p>
                                    </a>
                                </li>
                            <?php 
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        <?php
        }

        if (array_key_exists("ManageDistributors", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageDistributors.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#manageDistributorsSubItems">
                                Manage Distributors<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="manageDistributorsSubItems" class="panel-collapse collapse 
                    <?php echo in_array($pageTitle, array('Manage Distributors','Manage Distributor Transaction'))?'in':''; ?>">
                        <ul class="list-group">
                            <?php
                            if (array_key_exists("ManageDistributors", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Manage Distributors') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["ManageDistributors"];
                                }
                            ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/distributors/">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageDistributors.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Manage Distributors')?'para-new':''; ?>">
                                            Manage Distributors
                                        </p>
                                    </a>
                                </li>
                            <?php
                            }
                            ?>
                            
                        </ul>
                    </div>
                </div>
            </li>

        <?php
        } 

        if (array_key_exists("MonthlyExchangeRate", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClientsTransactions.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#MonthlyExchangeRateSubItems">
                                Monthly Exchange Rate<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="MonthlyExchangeRateSubItems" class="panel-collapse collapse 
                      
                     <?php echo in_array($pageTitle, array('Monthly Exchange Rate', 'Monthly Gaana Rate','Monthly Saavan Rate'))?'in':''; ?>"> 
                        <ul class="list-group">
                            <?php
                            if (array_key_exists("MonthlyExchangeRate", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Monthly Exchange Rate') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["MonthlyExchangeRate"];
                                }
                            ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/monthlyExchangeRate/">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClientsTransactions.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Monthly Exchange Rate')?'para-new':''; ?>">
                                            Monthly Exchange Rate
                                        </p>
                                    </a>
                                </li>
                            <?php 
                            }
                            ?>
                             <?php
                            if (array_key_exists("MonthlyExchangeRate", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Monthly Gaana Rate') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["MonthlyExchangeRate"];
                                }
                            ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/monthlyExchangeRate/gaanamonthlyv2.php">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClientsTransactions.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Monthly Gaana Rate')?'para-new':''; ?>">
                                        Monthly Gaana Rate
                                        </p>
                                    </a>
                                </li>
                            <?php 
                            }
                            ?>
                             <?php
                            if (array_key_exists("MonthlyExchangeRate", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Monthly Saavan Rate') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["MonthlyExchangeRate"];
                                }
                            ?>
                                <li class="navlink2 <?php echo $selectedTab; ?>">
                                    <a href="<?php echo $rootUrl; ?>views/monthlyExchangeRate/saavanmonthlyv2.php">
                                        <i>
                                            <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageClientsTransactions.png">
                                        </i>
                                        <p class="<?php echo ($pageTitle == 'Monthly Saavan Rate')?'para-new':''; ?>">
                                        Monthly Saavan Rate
                                        </p>
                                    </a>
                                </li>
                            <?php 
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        <?php
        }


        // if (array_key_exists("AccessControl", $modulesWithAccess)) {
        //     ?>
                 <!-- <li>
                     <div class="panel panel-default">
                         <div class="panel-heading <?php //if($pageTitle == 'Access Control') { echo 'selected-tab'; } ?>">
                             <h4 class="panel-title">
                                 <i>
                                     <img src="<?php //echo $rootUrl; ?>assets/img/sidebar/accessControl.png">
                                 </i>
                                 <a href="<?php //echo $rootUrl; ?>views/access-control/">Access Control</a>
                             </h4>
                         </div>
                     </div>
                 </li> -->
             <?php
        //     }
            




        if (array_key_exists("ManageAllocation", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageAllocation.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#manageAllocationSubItems">
                                Manage Allocation<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="manageAllocationSubItems" class="panel-collapse collapse 
                    <?php echo in_array($pageTitle, array('Upload MIS','Buy Allocation','Sell Allocation','Allocation Summary')) ?'in':'' ; ?>">
                        <ul class="list-group">
                            <?php
                            if (array_key_exists("UploadMIS", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Upload MIS') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["UploadMIS"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/upload-mis-files.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                    </i>
                                    <p class="<?php echo ($pageTitle == 'Upload MIS')?'para-new':''; ?>">
                                        Upload Holding Sheet/MIS
                                    </p>
                                </a>
                            </li>
                            <?php
                            }

                            if (array_key_exists("BuyAllocation", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Buy Allocation') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["BuyAllocation"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/manage-allocation.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageAllocation.png">
                                    </i>
                                    <p class="<?php echo ($pageTitle == 'Buy Allocation')?'para-new':''; ?>">
                                        Buy Allocation
                                    </p>
                                </a>
                            </li>
                            <?php
                            }

                            if (array_key_exists("SellAllocation", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Sell Allocation') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["SellAllocation"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/sell-allocation.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageAllocation.png">
                                    </i>
                                    <p class="<?php echo ($pageTitle == 'Sell Allocation')?'para-new':''; ?>">
                                        Sell Allocation
                                    </p>
                                </a>
                            </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        <?php
        } 
        
        if (array_key_exists("ManageDocument", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/Reports.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#manageDocumentSubItems">
                                Manage Documents<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="manageDocumentSubItems" class="panel-collapse collapse 
                    <?php echo in_array($pageTitle, array('Upload Contract Note','Performance Report')) ?'in':''; ?>">
                        <ul class="list-group">
                            <?php
                            if (array_key_exists("UploadContractNote", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Upload Contract Note') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["UploadContractNote"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/manage-document.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                    </i>
                                    <p class="<?php echo ($pageTitle == 'Upload Contract Note') ?'para-new':'' ;  ?>">
                                        Upload Contract Note
                                    </p>
                                </a>
                            </li>
                            <?php
                            }

                            if (array_key_exists("PerformanceReport", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Performance Report') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["PerformanceReport"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/performance-report.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                    </i>
                                    <p class="<?php echo ($pageTitle == 'Performance Report')?'para-new':''; ?>"
                                        >Performance Report
                                    </p>
                                </a>
                            </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        <?php
        }

        if (array_key_exists("ImportReports", $modulesWithAccess)) {
            ?>
                <li>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i>
                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                </i>
                                <a data-toggle="collapse" data-parent="#accordion" href="#ImportReportsSubItems">
                                    Reports<span class="caret"></span>
                                </a>
                            </h4>
                        </div>
                            <div id="ImportReportsSubItems" class="panel-collapse collapse 
                            <?php echo in_array($pageTitle, array('Import Reports', 'Validate Reports','Activate Reports','Assign Content Owner','Export Revenue Assets-id','Revenue Report Video-id'))?'in':''; ?>"> 
                                <ul class="list-group">
                                    <?php
                                    if (array_key_exists("ImportReports", $subModulesWithAccess)) {
                                        $selectedTab = "";
                                        if ($pageTitle=='Import Reports') {
                                            $selectedTab = "selected-tab";
                                            $userHighestPermOnPage = $subModulesWithAccess["ImportReports"];
                                        }
                                    ?>
                                        <li class="navlink2 <?php echo $selectedTab; ?>">
                                            <a href="<?php echo $rootUrl; ?>views/reports/">
                                                <i>
                                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                                </i>
                                                <p class="<?php echo ($pageTitle == 'Import Reports')?'para-new':''; ?>">
                                                    Import Reports
                                                </p>
                                            </a>
                                        </li>
                                    <?php 
                                    }

                                    if (array_key_exists("ValidateReports", $subModulesWithAccess)) {
                                        $selectedTab = "";
                                        if ($pageTitle=='Validate Reports') {
                                            $selectedTab = "selected-tab";
                                            $userHighestPermOnPage = $subModulesWithAccess["ValidateReports"];
                                        }
                                    ?>
                                        <li class="navlink2 <?php echo $selectedTab; ?>">
                                            <a href="<?php echo $rootUrl; ?>views/validate/">
                                                <i>
                                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                                </i>
                                                <p class="<?php echo ($pageTitle == 'Validate Reports')?'para-new':''; ?>">
                                                Validate Reports
                                                </p>
                                            </a>
                                        </li>
                                    <?php 
                                    }
                                    if (array_key_exists("ActivateReports", $subModulesWithAccess)) {
                                        $selectedTab = "";
                                        if ($pageTitle=='Activate Reports') {   
                                            $selectedTab = "selected-tab";
                                            $userHighestPermOnPage = $subModulesWithAccess["ActivateReports"];
                                        }
                                    ?>
                                        <li class="navlink2 <?php echo $selectedTab; ?>">
                                            <a href="<?php echo $rootUrl; ?>views/activate/">
                                                <i>
                                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                                </i>
                                                <p class="<?php echo ($pageTitle == 'Activate Reports')?'para-new':''; ?>">
                                                Activate Reports
                                                </p>
                                            </a>
                                        </li>
                                    <?php 
                                    }
                                    
                                     if (array_key_exists("ValidateReports", $subModulesWithAccess)) {
                                        $selectedTab = "";
                                        if ($pageTitle=='Assign Content Owner') {   
                                            $selectedTab = "selected-tab";
                                            $userHighestPermOnPage = $subModulesWithAccess["ValidateReports"];
                                        }
                                    ?>
                                        <li class="navlink2 <?php echo $selectedTab; ?>">
                                            <a href="<?php echo $rootUrl; ?>views/assign/">
                                                <i>
                                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                                </i>
                                                <p class="<?php echo ($pageTitle == 'Assign Content Owner')?'para-new':''; ?>">
                                                Assign Content Owner
                                                </p>
                                            </a>
                                        </li>
                                    <?php 
                                    }
                                    ?>

                                    <?php
                                    if (array_key_exists("ValidateReports", $subModulesWithAccess)) {
                                        $selectedTab = "";
                                        if ($pageTitle=='Export Revenue Assets-id') {   
                                            $selectedTab = "selected-tab";
                                            $userHighestPermOnPage = $subModulesWithAccess["ValidateReports"];
                                        }
                                    ?>
                                        <li class="navlink2 <?php echo $selectedTab; ?>">
                                            <a href="<?php echo $rootUrl; ?>views/export/">
                                                <i>
                                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                                </i>
                                                <p class="<?php echo ($pageTitle == 'Export Revenue Assets-id')?'para-new':''; ?>">
                                                Export Revenue Assets-id
                                                </p>
                                            </a>
                                        </li>
                                    <?php 
                                    }
                                    ?>
                                        <?php
                                    if (array_key_exists("ValidateReports", $subModulesWithAccess)) {
                                        $selectedTab = "";
                                        if ($pageTitle=='Revenue Report Video-id') {   
                                            $selectedTab = "selected-tab";
                                            $userHighestPermOnPage = $subModulesWithAccess["ValidateReports"];
                                        }
                                    ?>
                                        <li class="navlink2 <?php echo $selectedTab; ?>">
                                            <a href="<?php echo $rootUrl; ?>views/export/exportrevenuedetailv2.php">
                                                <i>
                                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                                </i>
                                                <p class="<?php echo ($pageTitle == 'Revenue Report Video-id')?'para-new':''; ?>">
                                                Revenue Report Video-id
                                                </p>
                                            </a>
                                        </li>
                                    <?php 
                                    }
                                    ?>
                                </ul>
                            
                
                        
                    </div>
                    
                </li>
            <?php
            }
            if (array_key_exists("ImportReports", $modulesWithAccess)) {
                $selectedTab = "";
                if ($pageTitle=='Publisher Reports') {
                    $selectedTab = "selected-tab";
                    $userHighestPermOnPage = $modulesWithAccess["ImportReports"];
                }
            ?>
                <li>
                    <div class="panel panel-default">
                        <div class="panel-heading <?php echo $selectedTab; ?>" >
                            <h4 class="panel-title">
                                <i>
                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                </i>
                                <a href="<?php echo $rootUrl; ?>views/activate/publisher_report.php">Publisher Report</a>
                            </h4>
                        </div>
                    </div>
                </li>
            <?php
            }
           
            if (array_key_exists("ImportReports", $modulesWithAccess)) {
                $selectedTab = "";
                if ($pageTitle=='Activity Log') {
                    $selectedTab = "selected-tab";
                    $userHighestPermOnPage = $modulesWithAccess["ImportReports"];
                }
            ?>
                <li>
                    <div class="panel panel-default">
                        <div class="panel-heading <?php echo $selectedTab; ?>" >
                            <h4 class="panel-title">
                                <i>
                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                </i>
                                <a href="<?php echo $rootUrl; ?>views/activatylog/activatylog.php">Activity Log</a>
                            </h4>
                        </div>
                    </div>
                </li>
            <?php
            }
            if (array_key_exists("ImportReports", $modulesWithAccess)) {
                $selectedTab = "";
                if ($pageTitle=='Report Export Log') {
                    $selectedTab = "selected-tab";
                    $userHighestPermOnPage = $modulesWithAccess["ImportReports"];
                }
            ?>
                <li>
                    <div class="panel panel-default">
                        <div class="panel-heading <?php echo $selectedTab; ?>" >
                            <h4 class="panel-title">
                                <i>
                                    <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadMis.png">
                                </i>
                                <a href="<?php echo $rootUrl; ?>views/activatylog/cron_activatylog.php">Report Export Log</a>
                            </h4>
                        </div>
                    </div>
                </li>
            <?php
            }
        if (array_key_exists("Reports", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/Reports.png">
                            </i>
                            <a data-toggle="collapse" data-parent="#accordion" href="#reportsSubItems">
                                Reports<span class="caret"></span>
                            </a>
                        </h4>
                    </div>
                    <div id="reportsSubItems" class="panel-collapse collapse 
                    <?php echo in_array($pageTitle, array('Management Fees','Performance Fees','Distributor Commission','Performance Reports'))?
                    'in':'' ; ?>">
                        <ul class="list-group">
                            <?php
                            if (array_key_exists("ManagementFees", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Management Fees') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["ManagementFees"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/management-fees.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageDistributors.png">
                                    </i>
                                    <p>Management Fees</p>
                                </a>
                            </li>
                            <?php
                            }

                            if (array_key_exists("PerformanceFees", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Performance Fees') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["PerformanceFees"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/performance-fees.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/uploadInputfiles.png">
                                    </i>
                                    <p>Performance Fees</p>
                                </a>
                            </li>
                            <?php
                            }

                            if (array_key_exists("DistributorCommission", $subModulesWithAccess)) {
                                $selectedTab = "";
                                if ($pageTitle=='Distributor Commission') {
                                    $selectedTab = "selected-tab";
                                    $userHighestPermOnPage = $subModulesWithAccess["DistributorCommission"];
                                }
                            ?>
                            <li class="navlink2 <?php echo $selectedTab; ?>">
                                <a href="<?php echo $rootUrl; ?>views/distributor-commission.php">
                                    <i>
                                        <img src="<?php echo $rootUrl; ?>assets/img/sidebar/manageDistributors.png">
                                    </i>
                                    <p>Distributor Commissions</p>
                                </a>
                            </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </li>
        <?php
        }
        
        if (array_key_exists("AccessControl", $modulesWithAccess)) {
        ?>
            <li>
                <div class="panel panel-default">
                    <div class="panel-heading <?php if($pageTitle == 'Access Control') { echo 'selected-tab'; } ?>">
                        <h4 class="panel-title">
                            <i>
                                <img src="<?php echo $rootUrl; ?>assets/img/sidebar/accessControl.png">
                            </i>
                            <a href="<?php echo $rootUrl; ?>views/access-control/">Access Control</a>
                        </h4>
                    </div>
                </div>
            </li>
        <?php
        }
        
        ?>
        
        
        </ul>
    </div>
</div>
</div>
