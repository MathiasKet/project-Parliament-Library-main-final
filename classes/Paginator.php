<?php
class Paginator {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $maxPagesToShow = 5;
    private $urlPattern;
    
    /**
     * Constructor
     * 
     * @param int $totalItems Total number of items
     * @param int $itemsPerPage Number of items per page
     * @param int $currentPage Current page number
     * @param string $urlPattern URL pattern for pagination links (use {page} as placeholder)
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage, $urlPattern = '') {
        $this->totalItems = (int)$totalItems;
        $this->itemsPerPage = (int)$itemsPerPage;
        $this->currentPage = (int)$currentPage;
        $this->urlPattern = $urlPattern;
        
        if ($this->itemsPerPage < 1) {
            $this->itemsPerPage = 10;
        }
        
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }
    }
    
    /**
     * Get the total number of pages
     * 
     * @return int Total pages
     */
    public function getTotalPages() {
        return (int)ceil($this->totalItems / $this->itemsPerPage);
    }
    
    /**
     * Get the current page number
     * 
     * @return int Current page
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Get the offset for SQL queries
     * 
     * @return int SQL offset
     */
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    /**
     * Get the limit for SQL queries
     * 
     * @return int SQL limit
     */
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    /**
     * Get the previous page number
     * 
     * @return int|null Previous page number or null if on first page
     */
    public function getPrevPage() {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }
        return null;
    }
    
    /**
     * Get the next page number
     * 
     * @return int|null Next page number or null if on last page
     */
    public function getNextPage() {
        if ($this->currentPage < $this->getTotalPages()) {
            return $this->currentPage + 1;
        }
        return null;
    }
    
    /**
     * Get an array of pagination links
     * 
     * @return array Array of page numbers with their URLs
     */
    public function getPages() {
        $pages = [];
        $totalPages = $this->getTotalPages();
        
        if ($totalPages <= 1) {
            return [];
        }
        
        // Always show first page
        $pages[] = [
            'num' => 1,
            'url' => $this->getPageUrl(1),
            'isCurrent' => (1 === $this->currentPage)
        ];
        
        // Calculate start and end of middle section
        $start = max(2, $this->currentPage - floor($this->maxPagesToShow / 2));
        $end = min($totalPages - 1, $start + $this->maxPagesToShow - 1);
        
        // Adjust if we're near the end
        if ($end - $start < $this->maxPagesToShow - 1) {
            $start = max(2, $end - $this->maxPagesToShow + 1);
        }
        
        // Add ellipsis if needed
        if ($start > 2) {
            $pages[] = ['ellipsis' => true];
        }
        
        // Add middle pages
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = [
                'num' => $i,
                'url' => $this->getPageUrl($i),
                'isCurrent' => ($i === $this->currentPage)
            ];
        }
        
        // Add ellipsis if needed
        if ($end < $totalPages - 1) {
            $pages[] = ['ellipsis' => true];
        }
        
        // Always show last page if there is more than one page
        if ($totalPages > 1) {
            $pages[] = [
                'num' => $totalPages,
                'url' => $this->getPageUrl($totalPages),
                'isCurrent' => ($totalPages === $this->currentPage)
            ];
        }
        
        return $pages;
    }
    
    /**
     * Generate URL for a specific page
     * 
     * @param int $pageNum Page number
     * @return string URL for the page
     */
    public function getPageUrl($pageNum) {
        if (empty($this->urlPattern)) {
            // If no pattern is provided, use query string
            $query = $_GET;
            $query['page'] = $pageNum;
            return '?' . http_build_query($query);
        }
        
        return str_replace('{page}', $pageNum, $this->urlPattern);
    }
    
    /**
     * Render HTML pagination controls
     * 
     * @param string $ulClass CSS class for the <ul> element
     * @param string $liClass CSS class for <li> elements
     * @param string $activeClass CSS class for active <li> element
     * @param string $disabledClass CSS class for disabled <li> element
     * @return string HTML for pagination controls
     */
    public function render($ulClass = 'pagination', $liClass = 'page-item', $activeClass = 'active', $disabledClass = 'disabled') {
        $pages = $this->getPages();
        
        if (empty($pages)) {
            return '';
        }
        
        $html = '<ul class="' . htmlspecialchars($ulClass) . '">';
        
        // Previous button
        $prevPage = $this->getPrevPage();
        $prevClass = $prevPage ? $liClass : $liClass . ' ' . $disabledClass;
        $html .= '<li class="' . $prevClass . '">';
        if ($prevPage) {
            $html .= '<a class="page-link" href="' . htmlspecialchars($this->getPageUrl($prevPage)) . '">&laquo; Previous</a>';
        } else {
            $html .= '<span class="page-link">&laquo; Previous</span>';
        }
        $html .= '</li>';
        
        // Page numbers
        foreach ($pages as $page) {
            if (isset($page['ellipsis'])) {
                $html .= '<li class="' . $liClass . ' disabled"><span class="page-link">...</span></li>';
                continue;
            }
            
            $pageClass = $page['isCurrent'] ? $liClass . ' ' . $activeClass : $liClass;
            $html .= '<li class="' . $pageClass . '">';
            
            if ($page['isCurrent']) {
                $html .= '<span class="page-link">' . $page['num'] . ' <span class="sr-only">(current)</span></span>';
            } else {
                $html .= '<a class="page-link" href="' . htmlspecialchars($page['url']) . '">' . $page['num'] . '</a>';
            }
            
            $html .= '</li>';
        }
        
        // Next button
        $nextPage = $this->getNextPage();
        $nextClass = $nextPage ? $liClass : $liClass . ' ' . $disabledClass;
        $html .= '<li class="' . $nextClass . '">';
        if ($nextPage) {
            $html .= '<a class="page-link" href="' . htmlspecialchars($this->getPageUrl($nextPage)) . '">Next &raquo;</a>';
        } else {
            $html .= '<span class="page-link">Next &raquo;</span>';
        }
        $html .= '</li>';
        
        $html .= '</ul>';
        
        return $html;
    }
}
