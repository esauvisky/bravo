Bravi Assessment Test
=====================

### Disclaimer

>*I've never used docker before, though I took the challenge — **at the expense of maybe failing the test** — just for the sake of learning. It looked like such a ridiculously useful tool, that I'm certain I'll continue to make use of it in my future projects, regardless of whether I pass this test or not. Keep in mind that I'm quite aware that the way I set it up for this project may look a bit funky, but that's to be expected as I'm still in the beggining of docker's learning curve.*

>*On the other hand, though, if anything regarding Wordpress looks a bit funky, well... that's simply because it's Wordpress.*

## Requirements

- `docker`

- `docker-compose`

## Instructions
1. Clone this repo and `cd` into the directory.

2. Run the docker application:

        docker-compose up -d

3. Open your browser and point it to `localhost`<sup>[[1]](#footnote1)</sup>.

4. Profit.

## Here are some suggestions to get you started

- Log in as `admin:admin`

- Log in as `user:user`

- Try cheating!

    - Open the [search page](http://localhost/search/) while not logged in — directly via the URL —, see if it works <sup>[[2]](#footnote2)</sup>.

## Remarks

- I've never used the theme Hetia(https://wordpress.org/themes/hestia/) before. I deliberately chose a random theme as a *proof-of-concept* that my solution would be robust enough to be suitable for any theme.

- OMDB API parameter *By search* returns paginated JSONs, which **was not implemented on the search page**. So you'll only get 10 results per search.

-

## Foot-Notes

<a name="footnote1">1</a>. If you're already running anything on port 80, you can map a different port by editing the file `docker-compose.yml`.

<a name="footnote2">2</a>. If you open any "Private" page without the correct permissions, Wordpress' default behaviour is to 404 on you. The proper way to solve it and give you control over what'll be displayed — e.g.: an alternative login page — would be to create a custom page template that handles the verification of whether the user is logged in or not, and use it as the template of the page(s) you're trying to restrict access. Setting pages as Private is much simpler and works perfectly fine, but it's definitely a workaround, which in the long run could get messy, particularly if you have lots of pages to restrict access to.
