ABOUT

"Eva" is short for "Entity Views Attachment;" it provides a Views display
plugin that allows the output of a View to be attached to the content of any
Drupal entity. The body of a node or comment, the profile of a user account,
or the listing page for a Taxonomy term are all examples of entity content.

The placement of the view in the entity's content can be reordered on the
"Field Display" administration page for that entity, like other fields added
using the Field UI module.

In addition, the unique ID of the entity the view is attached to -- as well as
any tokens generated from that entity -- can be passed in as arguments to the
view. For example, you might make a View that displays posts with an 'Author
ID' argument, then use Eva to attach the view to the User entity type. When a
user profile is displayed, the User's ID will be passed in as the argument to
the view magically.

That's right: magically.

Eva is powered by witchcraft.

HISTORY

Eva was originally developed by Jeff Eaton but never released. Larry Garfield
later cleaned it up and added the CCK integration, then released it under the
name 'Views Attach.' Endless confusion followed, as everyone thought it would
allow them to attach things to Views. Then Jeff Eaton refactored it for Drupal
7. Then they renamed it again, because they didn't want to write an upgrade
path.

Why *isn't* there an upgrade path? This version is built on top of Drupal 7's
Entity API as a single unified Views Display, while the D6 version juggled
NodeAPI and hook_user. While there's definitely feature parity, enough has
changed that cleanly upgrading a view from Views Attach 6.x-2.0 is essentially
impossible. They feel bad about it, and would accept patches that implemented
a well-tested upgrade path, but don't have the bandwidth to implement it
ourselves.

REQUIREMENTS

- Drupal 7
- Views 3

AUTHOR AND CREDIT

Original development: Jeff Eaton "eaton" (http://drupal.org/user/16496)

Actual D6 release, and version 2.0: Larry Garfield "Crell"
(http://drupal.org/user/26398)

D7 port and tomfoolery: Jeff Eaton "eaton" (http://drupal.org/user/16496)