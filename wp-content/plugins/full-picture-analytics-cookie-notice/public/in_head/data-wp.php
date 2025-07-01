<?php

$tracked_taxonomies = ['category', 'post_tag', 'post_format'];
if ( !empty( $this->main['tracked_taxonomies'] ) && is_array( $this->main['tracked_taxonomies'] ) ) {
    $tracked_taxonomies = array_merge( $tracked_taxonomies, $this->main['tracked_taxonomies'] );
}
//
// LOGIN STATUS AND USER ROLE
//
$fpdata['user'] = [];
if ( is_user_logged_in() ) {
    $user = wp_get_current_user();
    // login status
    $fpdata['user']['logged_in'] = true;
    // get user role
    $roles = (array) $user->roles;
    $fpdata['user']['role'] = $roles[0];
    // if user is not logged in
} else {
    // set role as visitor
    $fpdata['user']['role'] = 'Visitor';
    $fpdata['user']['logged_in'] = false;
}
if ( is_home() ) {
    // ! returns true for all subpages of post list
    $fpdata['page_type'] = 'Posts Home';
    $fpdata['page_title'] = get_the_title( get_option( 'page_for_posts', true ) );
} else {
    if ( is_singular() ) {
        global $post;
        $post_type = get_post_type();
        $post_type_obj = get_post_type_object( $post_type );
        if ( !empty( $post_type_obj->labels->singular_name ) ) {
            $fpdata['page_type'] = $post_type_obj->labels->singular_name;
        } else {
            if ( !empty( $post_type_obj->label ) ) {
                $fpdata['page_type'] = $post_type_obj->label;
            } else {
                $fpdata['page_type'] = $post_type;
            }
        }
        $fpdata['content_id'] = get_the_ID();
        $fpdata['page_title'] = $post->post_title;
        $fpdata['page_id'] = $post->ID;
        $fpdata['published'] = $post->post_date;
        // get author name and obsuscate it if it's an email
        $dirty_author_name = get_the_author_meta( 'display_name', (int) $post->post_author );
        if ( filter_var( $dirty_author_name, FILTER_VALIDATE_EMAIL ) ) {
            $fpdata['author_name'] = explode( '@', $dirty_author_name )[0];
        } else {
            $fpdata['author_name'] = $dirty_author_name;
        }
        // author ID (requires admin approval)
        if ( isset( $this->main['show_author_id'] ) ) {
            $fpdata['author_id'] = (int) $post->post_author;
        }
        // Post's taxonomy terms
        $taxonomies = get_object_taxonomies( $post );
        if ( is_array( $taxonomies ) && count( $taxonomies ) > 0 ) {
            $terms_tmp = [];
            foreach ( $taxonomies as $tax_slug ) {
                // make sure to include terms of taxonomies tracked by default and those chosen by the user
                if ( in_array( $tax_slug, $tracked_taxonomies ) ) {
                    $terms = get_the_terms( $post, $tax_slug );
                    if ( $terms ) {
                        foreach ( $terms as $term ) {
                            $term_data = [
                                'name'     => $term->name,
                                'slug'     => $term->slug,
                                'parent'   => $term->parent,
                                'taxonomy' => $term->taxonomy,
                            ];
                            array_push( $terms_tmp, $term_data );
                        }
                    }
                }
            }
            if ( count( $terms_tmp ) > 0 ) {
                $fpdata['terms'] = $terms_tmp;
            }
        }
        if ( is_single() ) {
            if ( is_singular( 'post' ) && is_sticky() ) {
                $fpdata['sticky'] = true;
            }
        } else {
            if ( is_page() ) {
                $fpdata['page_type'] = 'Page';
                if ( is_front_page() ) {
                    $fpdata['page_type'] = 'Front Page';
                } else {
                    if ( is_privacy_policy() ) {
                        $fpdata['page_type'] = 'Privacy Policy';
                    }
                }
                if ( $post->post_parent ) {
                    $fpdata['subpage'] = true;
                }
            }
        }
        if ( is_single() || is_page() ) {
            $paged = ( get_query_var( 'page' ) ? get_query_var( 'page' ) : false );
            // returns result only on 2+ page
            if ( $paged ) {
                $fpdata['page_number'] = $paged;
            }
        }
    } else {
        if ( is_archive() ) {
            // ! returns false on "home" page
            $term = get_queried_object();
            if ( !empty( $term ) && !empty( $term->term_id ) ) {
                $fpdata['content_id'] = $term->term_id;
            }
            if ( is_category() ) {
                $fpdata['page_type'] = 'Category';
                $fpdata['page_title'] = single_term_title( '', false );
            } else {
                if ( is_tag() ) {
                    $fpdata['page_type'] = 'Tag';
                    $fpdata['page_title'] = single_term_title( '', false );
                } else {
                    if ( is_tax() ) {
                        // true for terms of a custom taxonomy
                        $fpdata['page_type'] = 'Custom Taxonomy';
                        // get taxonomy name and term title on non archive
                        if ( $term ) {
                            // page type
                            $tax_slug = $term->taxonomy;
                            if ( !empty( $tax_slug ) ) {
                                $tax = get_taxonomy( $tax_slug );
                                $fpdata['page_type'] = ( !empty( $tax->labels->singular_name ) ? $tax->labels->singular_name : 'Custom Taxonomy' );
                            }
                            // term name
                            $term_name = $term->name;
                            if ( !empty( $term_name ) ) {
                                $fpdata['page_title'] = $term_name;
                            }
                        }
                    } else {
                        if ( is_author() ) {
                            $fpdata['page_type'] = 'Author Archive';
                            $term = get_queried_object();
                            if ( $term ) {
                                $fpdata['author'] = $term->data->display_name;
                                if ( !empty( $this->main['hide_author_id'] ) ) {
                                    $fpdata['author_id'] = $term->data->id;
                                }
                            }
                        } else {
                            if ( is_date() ) {
                                $fpdata['page_type'] = 'Date Archive';
                                if ( is_year() ) {
                                    $fpdata['page_type'] = 'Year Archive';
                                } else {
                                    if ( is_month() ) {
                                        $fpdata['page_type'] = 'Month Archive';
                                    } else {
                                        if ( is_day() ) {
                                            $fpdata['page_type'] = 'Day Archive';
                                        } else {
                                            if ( is_time() ) {
                                                $fpdata['page_type'] = 'Time Archive';
                                            }
                                        }
                                    }
                                }
                            } else {
                                // check if it is a CPT archive
                                $fpdata['page_type'] = 'Archive';
                                $term = get_queried_object();
                                if ( $term ) {
                                    $term_name = $term->label;
                                    if ( !empty( $term_name ) ) {
                                        $fpdata['page_title'] = $term_name;
                                        $fpdata['page_type'] = $term_name . ' Archive';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ( is_search() ) {
                $fpdata['page_type'] = 'Search';
                $search_query = get_search_query();
                if ( $search_query ) {
                    $fpdata['search_query'] = $search_query;
                }
                global $wp_query;
                $fpdata['search_results'] = $wp_query->found_posts;
                $fpdata['page_title'] = 'Search results';
            }
        }
    }
}
//
// PAGED ARCHIVES
//
if ( (is_home() || is_archive() || is_search()) && is_paged() ) {
    // returns true on archive pages and home (page 2+)
    $archive_page = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : false );
    if ( $archive_page !== false ) {
        $fpdata['page_number'] = $archive_page;
    }
}
if ( is_404() ) {
    $fpdata['page_type'] = '404';
    $fpdata['page_title'] = '404';
}