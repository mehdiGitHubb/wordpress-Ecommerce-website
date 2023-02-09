# Human Readable Interface
[![Continuous Integration](https://github.com/Dhii/human-readable-interface/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/Dhii/human-readable-interface/actions/workflows/continuous-integration.yml)
[![Latest Stable Version](https://poser.pugx.org/dhii/human-readable-interface/version)](https://packagist.org/packages/dhii/human-readable-interface)
[![Latest Unstable Version](https://poser.pugx.org/dhii/human-readable-interface/v/unstable)](//packagist.org/packages/dhii/human-readable-interface)

Interfaces for human-readable string interoperation.

## Details
Use the interfaces in this package to represent things that expose
human-readable strings. Then perhaps base rendering logic on them.

## Interfaces
- [`CaptionAwareInterface`][] - Something that has a caption.
  Usually a descriptive sub-heading. Perhaps for images.
- [`DescriptionAwareInterface`][] - Something that has a description.
  Should describe the object. Settings fields usually use this.
- [`LabelAwareInterface`][] - Something that has a label.
  This is something that visually names the object. Setting fields
  usually use this, or maybe a tab or a menu item
- [`MessageAwareInterface`][] - Something that has a message.
  Perhaps a notification object.
- [`TitleAwareInterface`][] - Something that has a title.
  Could be page.


[`CaptionAwareInterface`]: src/CaptionAwareInterface.php
[`DescriptionAwareInterface`]: src/DescriptionAwareInterface.php
[`LabelAwareInterface`]: src/LabelAwareInterface.php
[`MessageAwareInterface`]: src/MessageAwareInterface.php
[`TitleAwareInterface`]: src/TitleAwareInterface.php
