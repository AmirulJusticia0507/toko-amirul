
<?php
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                    $currentPage = basename($_SERVER['PHP_SELF']); // Mendapatkan halaman saat ini
                
                    switch ($page) {
                        case 'productmanagement':
                            if ($currentPage !== 'productmanagement.php') {
                                header("Location: productmanagement.php?page=productmanagement");
                                exit;
                            }
                            break;
                
                        case 'cart':
                            if ($currentPage !== 'cart.php') {
                                header("Location: cart.php?page=cart");
                                exit;
                            }
                            break;
                
                        case 'profile.php':
                            if ($currentPage !== 'profile.php.php') {
                                header("Location: profile.php.php?page=profile.php");
                                exit;
                            }
                            break;
                
                        case 'categories':
                            if ($currentPage !== 'categories.php') {
                                header("Location: categories.php?page=categories");
                                exit;
                            }
                            break;

                        case 'brands':
                            if ($currentPage !== 'brands.php') {
                                header("Location: brands.php?page=brands");
                                exit;
                            }
                            break;
                        
                            case 'customerproducts':
                            if ($currentPage !== 'customerproducts.php') {
                                header("Location: customerproducts.php?page=customerproducts");
                                exit;
                            }
                            break;

                            
                        case 'dashboard':
                            if ($currentPage !== 'index.php') {
                                header("Location: index.php?page=dashboard");
                                exit;
                            }
                            break;
                
                        default:
                            // Handle cases for other pages or provide a default action
                            break;
                    }
                }
                ?>