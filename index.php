<?php
    require_once('config.php');
    $setting = new Setting();
    $siteName = $setting->getSettingValue('site_name');
    if ($siteName == '') {
        $siteName = 'Knowledge Base';
    }
    $setting = new Setting();
    $maintenanceMode = $setting->getSettingValue('maintenance_mode');
    if ($maintenanceMode == 'enabled') {
        $setting = new Setting();
        $maintenanceMessage = $setting->getSettingValue('maintenance_message');
        if ($maintenanceMessage == '') {
            $maintenanceMessage = 'The Knowledge Base is undergoing maintenance, please check back later.';
        }
        require_once('header.php');
        echo '<div class="container"><div class="alert alert-primary" role="alert">'.$maintenanceMessage.'</div></div>';
        require_once('footer.php');
        exit;
    } else {
        if (isset($_GET['page']) && $_GET['page'] === 'category') {
            /* Display Category */
            if (isset($_GET['c'])) {
                $get = htmlspecialchars($_GET['c'], ENT_QUOTES, 'UTF-8');
                $category = new Category();
                $categoryData = $category->getCategoryBySlug($get);
                if (!$categoryData) {
                    header('Location: index.php?msg=error');
                    exit;
                } else {
                    $pageTitle = $categoryData['name'];
                    require_once('header.php');
                ?>
                    <div class="container">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Knowledge Base</a></li>
                            <?php
                                if ($categoryData['parent'] != NULL) {
                                    $categoryInner = new Category();
                                    $parentCategory = $categoryInner->getCategory($categoryData['parent']);
                                    echo '<li class="breadcrumb-item"><a href="index.php?page=category&c=' . $parentCategory['slug'] . '">' . $parentCategory['name'] . '</a></li>';
                                }
                            ?>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo($pageTitle); ?></li>
                        </ol>
                        <header>
                            <h1><?php echo $pageTitle; ?></h1>
                            <p><?php echo $categoryData['description']; ?></p>
                        </header>
                        <main>
                            <?php
                                $categorySub = new Category();
                                $subCategories = $categorySub->getAllCategoriesWithParent($categoryData['id']);
                                if (!$subCategories) {
                                    $hasSubCategories = false;
                                } else {
                                    $hasSubCategories = true;
                                    foreach($subCategories as $subCategory) {
                                        echo '
                                        <div class="article-item">
                                            <a href="index.php?page=category&c=' . $subCategory['slug'] . '">
                                                <div>
                                                    <h6><i class="bi bi-' . $subCategory['icon'] . '"></i>  ' . $subCategory['name'] . '</h6>
                                                    <p>' . $subCategory['description'] . '</p>
                                                </div>
                                            </a>
                                        </div>
                                        ';
                                    }
                                }
                            ?>
                            <?php
                                $article = new Article();
                                $articles = $article->getArticlesEnabledByCategoryId($categoryData['id']);
                                if (!$articles) {
                                    if (!$hasSubCategories) {
                                        echo '<p><i>No content in this category.</i></p>';
                                    }
                                } else {
                                    foreach($articles as $article) {
                                        echo '
                                        <div class="article-item">
                                            <a href="index.php?page=article&a=' . $article['slug'] . '">
                                                <div>
                                                    <h6><i class="bi bi-file-earmark"></i>  ' . $article['title'] . '</h6>
                                                </div>
                                            </a>
                                        </div>
                                        ';
                                    }
                                }
                            ?>
                        </main>
                    </div>
                <?php
                    require_once('footer.php');
                }
            } else {
                header('Location: index.php?msg=error');
                exit;
            }
        } else if (isset($_GET['page']) && $_GET['page'] === 'article') {
            /* Display Article */
            if (isset($_GET['a'])) {
                $get = htmlspecialchars($_GET['a'], ENT_QUOTES, 'UTF-8');
                $article = new Article();
                $articleData = $article->getArticleBySlug($get);
                if (!$articleData) {
                    header('Location: index.php?msg=error');
                    exit;
                } else {
                    $pageTitle = $articleData['title'];
                    require_once('header.php');
                ?>
                    <div class="container">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Knowledge Base</a></li>
                            <?php
                                $category = new Category();
                                $categoryData = $category->getCategory($articleData['category']);
                            ?>
                            <?php
                                if ($categoryData['parent'] != NULL) {
                                    $categoryInner = new Category();
                                    $parentCategory = $categoryInner->getCategory($categoryData['parent']);
                                    echo '<li class="breadcrumb-item"><a href="index.php?page=category&c=' . $parentCategory['slug'] . '">' . $parentCategory['name'] . '</a></li>';
                                }
                            ?>
                            <li class="breadcrumb-item"><a href="index.php?page=category&c=<?php echo($categoryData['slug']); ?>"><?php echo($categoryData['name']); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo($pageTitle); ?></li>
                        </ol>
                        <header>
                            <h1><?php echo $pageTitle; ?></h1>
                        </header>
                        <main>
                            <?php echo $articleData['content']; ?>
                        </main>
                    </div>
                <?php
                    require_once('footer.php');
                }
            } else {
                header('Location: index.php?msg=error');
                exit;
            }
        } else {
            /* Home Page */
            $pageTitle = 'Categories';
            require_once('header.php');
            ?>
            <div class="container">
                <?php
                    if (isset($_GET['msg']) && $_GET['msg'] === 'error') {
                        echo '<div class="alert alert-danger" role="alert">Resource not found, returning to home page.</div>';
                    }
                ?>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Knowledge Base</li>
                </ol>
                <?php
                    $category = new Category();
                    $categories = $category->getAllCategoriesEnabledTopLevel();
                    if (!$categories) {
                        echo '<p><i>No categories present.</i></p>';
                    } else {
                        foreach($categories as $category) {
                            $article = new Article();
                            $numArticlesInCategory = $article->getNumberOfArticlesEnabledByCategoryId($category['id']);
                            echo '
                            <div class="category-item">
                                <a href="index.php?page=category&c=' . $category['slug'] . '">
                                    <div class="category-inner">
                                        <div class="category-icon">
                                            <i class="bi bi-' . $category['icon'] . '"></i>
                                        </div>
                                        <div class="category-content">
                                            <h6>' . $category['name'] . ' <span class="num-articles">(' . $numArticlesInCategory . ')</span></h6>
                                            <p>' . $category['description'] . '</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            ';
                        }
                    }
                    
                ?>
            </div>
            
            
        <?php 
            require_once('footer.php');
            
        }
           
    }
    
?>
