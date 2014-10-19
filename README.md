wordprocessor
=============


**Usage:**

'prompt$ cat {path_to_file} | php wordprocessor.php 1' : Pipe text to STDIN

'prompt$ php wordprocessor.php 2 {num_of_words}' : Generates words randomly from within PHP

'prompt$ php wordprocessor.php 3' : GETs 1000 words from random.org API


**Sample Output - with included text file: The Project Gutenberg EBook of The Complete Works of William Shakespeare**

$ cat ws.txt | php wordprocessor.php 1

Array(

    [﻿the] => 1
    
    [project] => 331
    
    [gutenberg] => 326
    
    [ebook] => 17
    
    [of] => 18307
    
    [the] => 27842
    
    [complete] => 248
    
    [works] => 284
    
    [william] => 354
    
    [shakespeare] => 272
    
    ...
    
    [locating] => 1
    
    [gutindex] => 1)
    
** WordMapCount: 23802

** RunTime: 8 secs
