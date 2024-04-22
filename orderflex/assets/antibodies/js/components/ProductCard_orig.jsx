import React, { Component } from "react";
//import { Table } from "reactstrap";
//import Card from 'react-bootstrap/Card';
//import NewProductModal from "./NewProductModal";
//import '../../css/card.css';

//import ConfirmRemovalModal from "./ConfirmRemovalModal";
//https://mdbootstrap.com/docs/standard/extended/card-deck/

//<img src="https://mdbcdn.b-cdn.net/img/new/standard/city/043.webp" className="card-img-top" alt="Hollywood Sign on The Hill" />
//class ProductTable extends Component {
  //render() {
const ProductTable = ({ product, setref }) => {
    //const product = this.props.product;

    const cardstyle = {
       // width: "100%",
      //marging: "1.5rem",
      //   'max-width: 100%'
        //color: 'white',
        //fontSize: 5
        minWidth: "20%",
        flexGrow: 0
    };

    //https://sentry.io/answers/react-for-loops/
    let imageList = [];
    product.documents.forEach((imageUrl, index) => {
        imageList.push(
             <li key={index}>{imageUrl}</li>
            //<img src={imageUrl} className="card-img-top" alt="Antibody Image"/>
        );
    });

    // <div ref={setref} key={product.pk} className="card h-100" style={cardstyle}>

    return (

        <div className="col">
        <div ref={setref} key={product.pk} className="card h-100" style={cardstyle}>

            {imageList}

            <div className="card-body">
              <div className="card-text">

                    {/*
                    <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.id } disabled />
                      <label>ID</label>
                    </div>

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.name } disabled />
                      <label>Name</label>
                  </div>

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.description } disabled />
                      <label>Description</label>
                  </div>

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.categorytags } disabled />
                      <label>Category Tags</label>
                  </div>
                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.company } disabled />
                      <label>Company</label>
                  </div>

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.host } disabled />
                      <label>Host</label>
                  </div>

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.reactivity } disabled />
                      <label>Reactivity</label>
                  </div>

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.storage } disabled />
                      <label>Storage</label>
                  </div>
                  */}

                  <div className="form-floating mb-3 mt-3">
                      <input type="text" className="form-control" value={ product.publictext } disabled />
                      <label>Description</label>
                  </div>

              </div>

              <div className="card-footer">
                <small className="text-muted">Last updated 3 mins ago</small>
              </div>

{/*
              {% if user.is_authenticated and product.user.id and user.id == product.user.id %}
                <a className="btn btn-info" href="{% url 'products_management_edit' id=product.id %}">Edit</a>
                <button className="btn btn-secondary" href="#" onclick="deleteProduct(this,{{product.id}});">Delete</button>
              {% endif %}
*/}

            </div>
        </div>
        </div>
    );
  //}
}

export default ProductTable;

