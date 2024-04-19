import React, { Component } from "react";
import { Table } from "reactstrap";
//import NewProductModal from "./NewProductModal";
import '../../css/card.css';

//import ConfirmRemovalModal from "./ConfirmRemovalModal";
//https://mdbootstrap.com/docs/standard/extended/card-deck/

//class ProductTable extends Component {
  //render() {
const ProductTable = ({ product, setref }) => {
    //const product = this.props.product;

    const cardstyle = {
      //width: "45rem",
      //marging: "1.5rem",
    };

    return (

        <div className="col">
        <div ref={setref} key={product.pk} className="card h-100" style={cardstyle}>


            <img src="https://mdbcdn.b-cdn.net/img/new/standard/city/043.webp" className="card-img-top" alt="Hollywood Sign on The Hill" />
             

            <div className="card-body">
              <div className="card-text">

                <div className="form-floating mb-3 mt-3">
                  <input type="text" className="form-control" value={ product.name } disabled />
                  <label>Name</label>
                </div>
                <div className="form-floating mb-3 mt-3">
                  <input type="text" className="form-control" value={ product.description } disabled />
                  <label>Description</label>
                </div>
                <div className="form-floating mb-3 mt-3">
                  <input type="text" className="form-control" value={ product.unitPrice } disabled />
                  <label>Price</label>
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

