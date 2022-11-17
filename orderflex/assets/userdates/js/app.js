import React from 'react';
import ReactDOM from 'react-dom';

import Items from './Components/Items';


class App extends React.Component {
    constructor() {
        super();

        this.state = {
            entries: []
        };
    }

    componentDidMount() {
        fetch('https://jsonplaceholder.typicode.com/posts/')
            .then(response => response.json())
            .then(entries => {
                this.setState({
                    entries
            });
        });
    }

    render() {
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
}

ReactDOM.render(<App />, document.getElementById('root'));
