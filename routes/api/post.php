<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('post')
            ->namespace('Post')
            ->group(function() {

                /**
                 * @param {string} slugOrId
                 */
                Route::get('/post', [
                    'as' => 'api.frontend.post.posts.get',
                    'uses' => 'PostController@get',
                ]);

                /**
                 * @param {int} page
                 * @param {int} per_page
                 * @param {string} categories comma-delimited category ids
                 */
                Route::get('/posts', [
                    'as' => 'api.frontend.post.posts',
                    'uses' => 'PostController@posts',
                ]);

                /**
                 * @param {int} page
                 * @param {int} per_page
                 * @param {int} parent_id
                 */
                Route::get('/categories', [
                    'as' => 'api.frontend.post.categories',
                    'uses' => 'PostCategoryController@categories',
                ]);

                /**
                 * @param {string} slugOrId
                 */
                Route::get('/category', [
                    'as' => 'api.frontend.post.categories.get',
                    'uses' => 'PostCategoryController@get',
                ]);
            });
    });
