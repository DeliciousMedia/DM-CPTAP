# DM CPTAP

A WordPress plugin which provides a user interface to save content for use on a custom post type archive page.

## Usage

Add support for `dmcptap_archive_page` to your post type either when defining it or via `add_post_type_support`.

Use `dmcptap_get_content_item` to retrieve the relevant content item.

## Notes

Data is stored in the wp_options table; creating an option per-post type 

## Todo

- Add Gutenberg support.
- Ability to add arbitrary fields.
- Option to remove data when deactivating.

---
Built by the team at [Delicious Media](https://www.deliciousmedia.co.uk/), a specialist WordPress development agency based in Sheffield, UK.