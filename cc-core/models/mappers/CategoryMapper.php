<?php

class CategoryMapper
{
    public function getCategoryById($categoryId)
    {
        $db = Registry::get('db');
        $query = 'SELECT * FROM ' . DB_PREFIX . 'categories WHERE categoryId = :categoryId';
        $dbResults = $db->fetchRow($query, array(':categoryId' => $categoryId));
        if ($db->rowCount() == 1) {
            return $this->_map($dbResults);
        } else {
            return false;
        }
    }

    protected function _map($dbResults)
    {
        $category = new Category();
        $category->categoryId = $dbResults['categoryId'];
        $category->name = $dbResults['name'];
        $category->slug = $dbResults['slug'];
        return $category;
    }

    public function save(Category $category)
    {
        $category = Plugin::triggerFilter('video.beforeSave', $category);
        $db = Registry::get('db');
        if (!empty($category->categoryId)) {
            // Update
            Plugin::triggerEvent('video.update', $category);
            $query = 'UPDATE ' . DB_PREFIX . 'categories SET';
            $query .= ' name = :name, slug = :slug';
            $query .= ' WHERE categoryId = :categoryId';
            $bindParams = array(
                ':categoryId' => $category->categoryId,
                ':name' => $category->name,
                ':slug' => $category->slug
            );
        } else {
            // Create
            Plugin::triggerEvent('video.create', $category);
            $query = 'INSERT INTO ' . DB_PREFIX . 'categories';
            $query .= ' (name, slug)';
            $query .= ' VALUES (:name, :slug)';
            $bindParams = array(
                ':name' => $category->name,
                ':slug' => $category->slug
            );
        }
            
        $db->query($query, $bindParams);
        $categoryId = (!empty($category->categoryId)) ? $category->categoryId : $db->lastInsertId();
        Plugin::triggerEvent('video.save', $categoryId);
        return $categoryId;
    }
}