import React from 'react';
import ReactDOM from 'react-dom';

import Items from './Components/Items';
import Photos from './Components/Photos';

//https://www.cloudways.com/blog/symfony-react-using-webpack-encore/
//https://www.twilio.com/blog/building-a-single-page-application-with-symfony-php-and-react

class App extends React.Component {
    constructor() {
        super();

        this.state = {
            entries: []
        };

        this.dataurl = "https://jsonplaceholder.typicode.com/photos";
        //this.dataurl = "https://jsonplaceholder.typicode.com/posts/";
    }

    componentDidMount() {
        fetch(this.dataurl)
            .then(response => response.json())
            .then(entries => {
                this.setState({
                    entries
            });
        });
    }

    render_orig() {
        return (
            <div className="row">
                {this.state.entries.map(
                    ({ id, title, body }) => (
                        <Items
                            key={id}
                            title={title}
                            body={body}
                        >
                        </Items>
                    )
                )}
            </div>
        );
    }

    render() {
        return (
            <div className="row">
                {this.state.entries.map(
                    ({ id, title, url, thumbnailUrl }) => (
                        <Items
                            key={id}
                            title={title}
                            url={url}
                            thumbnailUrl={thumbnailUrl}
                        >
                        </Items>
                    )
                )}
            </div>
        );
    }
}

ReactDOM.render(<App />, document.getElementById('root'));
