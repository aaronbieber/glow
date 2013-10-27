# Chroma Controller

Control your Philips Hue lights through a simple web interface.

At this point, it doesn't walk you through configuring the connection to your 
bridge, nor does all of the object-oriented code completely make sense, but, 
on the plus side, it does work.

Twitter Bootstrap is used as the layout engine and performance on Mobile 
Chrome is excellent. Haven't tried other mobile browsers or tablets but things 
should work even if the layout isn't perfect.

If it seems over-engineered it's because this is a hobby site and a guinea pig 
for exercising some MVC/OOP ideas, so yeah, it probably could be written more 
simply but it is what it is.

## Features

Read and display the current state of your lights, toggle lights on and off, 
select color temperature and brightness or hue, saturation, and brightness 
settings for each light.

Save the current light settings as a "scene" stored in a local YAML file (just 
to keep things simple).

## Coming in Later Versions

The ability to actually edit the scene names, re-arrange scenes and lights, 
delete scenes, and so on.

Better error handling would be nice; sometimes selecting a scene appears to 
work but some lights don't change. It seems like success responses are being 
returned for those lights so I'm not sure if there is much the application can 
do, but perhaps it could do a read of the full light status to see if it 
matches what we expect.
